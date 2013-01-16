<?php
namespace Qwik\Kernel\App;

use Qwik\Kernel\App\Language;
use Qwik\Kernel\Environment\Environment;
use Qwik\Kernel\Template\TemplateProxy;

class AppManager {

    /**
     * @var AppManager Singleton
     */
    private static $instance;


    /**
     * @var Site\Site Site en cours
     */
    private $site;
    /**
     * @var \Qwik\Kernel\App\Routing\RouterManager
     */
    private $routerManager;
    /**
     * @var bool Mode debug ou pas
     *//*
    private $isDebug;*/
    /**
     * @var string url de base (Path relatif) du site. En général "/", mais parfois, il se peut qu'on ai mis le site bien plus bas.
     */
    private $baseUrl;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * Récupération du singleton, avec un test, car il faut être passé par init
     * @return AppManager
     * @throws \Exception
     */
    public static function getInstance(){
		if(is_null(self::$instance)){
			throw new \Exception('You must call init first!');
		}
		return self::$instance;
	}

    /**
     * Création de l'instance du singleton de la classe
     * @param $www string chemin vers le dossier www (là où se trouve notre fichier index.php) (path absolut)
     * @param $domain string nom de (sous) domaine
     * @return AppManager
     */
    public static function init($www){
        $domain = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);
        self::$instance = new AppManager($www, $domain);
        return self::$instance;
	}

    /**
     * Initialise la varibale debug en fonction du nom du domaine. Si ca commence par dev., on est en mode debug :)
     * @param $domain
     */
    //TODO: faire autrement, faire un array de config par env
    /*private function initDebug($domain){
		//On est en debug si notre domaine commence par dev.
		$this->isDebug = strpos($domain, 'dev.') === 0;	
	}*/

    /**
     * @return bool
     */
/*    public function isDebug(){
		return $this->isDebug;
	}*/

    /**
     * Récupération du nom de domain cleané (sans dev. s'il y en avait un)
     * @param $domain Nom de domaine (peut avoir un dev.) devant
     * @return string
     */
    private function getProperDomain($domain){

		return (strpos($domain, 'dev.') === 0) ? substr($domain, 4) : $domain;
	}

    /**
     * @param \Qwik\Kernel\Environment\Environment $environment
     */
    private function setEnvironment(Environment $environment){
        $this->environment = $environment;
    }

    /**
     * @return Environment
     */
    public function getEnvironment(){
        return $this->environment;
    }
    /**
     * @param $www string là où se trouve notre fichier index.php (path absolut)
     * @param $domain string
     */
    private function __construct($www, $domain){


        //initialisation du path dans lequel on se trouve (le plus souvent "/")
        $this->initBase();

        $www = (string) $www;

        //On récupère le bon domaine
		$domain = $this->getProperDomain((string) $domain);

        //Récupération du site
		$siteManager = new \Qwik\Kernel\App\Site\SiteManager();
		$this->site = $siteManager->getByPath($www, $domain);

        //Si c'est un alias, alors on va rediriger  via un header
        if($this->getSite()->getRedirect() != ''){
            //TODO: Voir si on peut faire confiance à $_SERVER['REDIRECT_URL']
            header('Location: http://'.$this->getSite()->getRedirect() . $_SERVER['REDIRECT_URL']);
            exit();

            //Problème si on fait juste ca, on aura notre url rewriting qui va foirer (à moins de faire de liens symboliques lors de la création du nom de domaine)
            //$this->site = $siteManager->getByPath($www, $domain);
        }

        //Si le site existe pas, alors on va prendre default pour afficher une page standard (oops, ce site n'existe pas encore)
		if(!$this->getSite()->exists()){
			$this->site = $siteManager->getByPath($www, 'default');
		}


		//Initialisation de la langue en cours
		Language::init($this->getSite()->getLanguages());

        //Initialisation de l'app
		$this->initApp();
	}


    private function initBase(){

        //Init base url
        $this->initBaseUrl();
        //Set de l'environnement
        $this->initEnvironment();

    }



    /**
     * @param $baseUrl
     */
    private function setBaseUrl($baseUrl){
        $this->baseUrl = (string) $baseUrl;
    }

    /**
     * @return string
     */
    public function getBaseUrl(){
        return $this->baseUrl;
    }

    /**
     * @return Site\Site
     */
    public function getSite(){
		return $this->site;
	}


    /**
     * Initialise l'app
     */
    private function initApp(){
		//Initialisation du routerManager
		$this->routerManager = new \Qwik\Kernel\App\Routing\RouterManager($this->getBaseUrl());

        //Initialise le moteur de template
        TemplateProxy::init($this);

        //Initialise les routes de de base
		$this->initRoutes();
	}

    /**
     * Ajoute les routes des modules
     */
    private function addModulesRoutes(){
		foreach(scandir(\Qwik\Kernel\App\Module\Module::getModulesPath()) as $moduleName){
			if($moduleName != '.' && $moduleName != '..'){
				try{
					$className = \Qwik\Kernel\App\Module\Module::getClassName($moduleName);
					//Ajout de la route par la méthode static "addRoute" du module
					call_user_func_array(array($className, 'injectInApp'), array($this, $this->getSite()));
				}catch(\Exception $ex){
					continue;
				}
			}
		}
	}

    /**
     * Initialise les routes
     * @throws Page\PageNotFoundException
     */
    private function initRoutes(){
		$site = $this->getSite();


		//Arrivée sur le site, j'attends une redirection vers la première page
        $this->getRouterManager()->get('root', '/', function() use ($site){

            //Récupération de la première page
            $pageManager = new \Qwik\Kernel\App\Page\PageManager();
            $firstPage = $pageManager->findFirst($site);

            //Si pas de page, alors 404
            if(!$firstPage){
                throw new \Qwik\Kernel\App\Page\PageNotFoundException();
            }

            //TODO: faire un ResponseRedirect
            //TODO: création d'une url avec un nom de route + variables (twig aussi)
            header('Location: ' . $this->getBaseUrl() . Language::get() . '/' . $firstPage->getUrl());
            exit();
        });
        
        //J'ai choisi une langue, mais pas de page, j'attends une redirection vers la première page
        //TODO: Code dupliqué par rapport au "/". Faire quelque chose
        $this->getRouterManager()->get('root_language', '/{_locale}', function($_locale) use($site){

            //Si on gère la langue, alors on va sur la première page
            if(in_array($_locale, $site->getLanguages())){
                $pageManager = new \Qwik\Kernel\App\Page\PageManager();
                $firstPage = $pageManager->findFirst($site);

                //Si pas de "première" page, alors Exception!
                if(!$firstPage){
                    throw new \Qwik\Kernel\App\Page\PageNotFoundException();
                }
                //Response redirect
                //TODO: faire un ResponseRedirect
                //TODO: création d'une url avec un nom de route + variables (twig aussi)
                header('Location: ' . $this->getBaseUrl() . Language::get() . '/' . $firstPage->getUrl());
                exit();

            }else{ //Sinon Exception :)
                throw new \Qwik\Kernel\App\Page\PageNotFoundException();
            }
        })->assert('_locale','[a-z]{2}');

        //J'ai une langue et une page :)
        $this->getRouterManager()->get('page', '/{_locale}/{pageName}', function($_locale, $pageName) use($site){
            //Changement de la langue quand c'est possible...
            Language::changeIfPossible($_locale);

            $pageManager = new \Qwik\Kernel\App\Page\PageManager();

            //Si page est vide c'est qu'on a fait par exemple /fr/, donc il faut récupérer la première page
            if($pageName === ''){
                $page = $pageManager->findFirst($site);
            }else{
                $page = $pageManager->findOneByUrl($site, $pageName);
            }

            //Si pas de page, alors 404
            if(!$page){
                throw new \Qwik\Kernel\App\Page\PageNotFoundException();
            }
            return TemplateProxy::getInstance()->renderPage($page);

        })->assert('_locale','[a-z]{2}');

        //Clear varnish et les templates
        $this->getRouterManager()->get('cc', '/admin/cc', function() use($site){

            //Clear Vanish, que si on a curl init
            if(function_exists('curl_init')){
                //Clear varnish
                header("Cache-Control: max-age=1"); // don't cache ourself

                //error_reporting(E_ALL);
                //ini_set("display_errors", 1);

                // Set to true to hide varnish result
                define("SILENT", false);

                if ( $ch = curl_init("http://" .$site->getDomain() . "/") ) {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PURGE");
                    curl_setopt($ch, CURLOPT_NOBODY, SILENT);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }


            //Clear du template
            TemplateProxy::getInstance()->clearCache();

            $pageManager = new \Qwik\Kernel\App\Page\PageManager();
            $page = $pageManager->findFirst($site);

            //Si pas de page, alors 404
            if(!$page){
                throw new \Qwik\Kernel\App\Page\PageNotFoundException();
            }
            return TemplateProxy::getInstance()->renderPage($page);
        });
        
        //On va voir si les modules on des routes
		$this->addModulesRoutes();
	}

    /**
     * @return Routing\RouterManager
     */
    public function getRouterManager(){
        return $this->routerManager;
    }


    /**
     * Initialise la "base url", C'est à dire ce qui va être "prepend" aux urls de chaque lien
     * Pratique, on retient le baseUrl, pour pouvoir l'utiliser comme préfix. Du coup, on peut mettre l'appli où on veut!
     * La différence avec $www, c'est qu'ici, il nous faut le path relatif à la racine htdocs
     */
    //TODO: faire avec getEnv, une classe request
    private function initBaseUrl(){

        //Récupération de là où se trouve le script
        $baseUrl = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_NAME']));

        //Si c'est pas juste un slash, alors c'est par exemple /monDossier/ok. Il faut donc rajouter un slash au bout
        if($baseUrl !== '/'){
            $baseUrl = $baseUrl . '/';
        }

        if(isset($_SERVER['PATH_INFO'])){ //Si path_info, alors on a fait un index.php/... ou dev.php/... du coup on prend le nom du fichier + slash à la fin
            $baseUrl .= basename($_SERVER['SCRIPT_NAME']) .'/';
        }else{
            //Si on a juste demandé /index.php ou /dev.php, alors on dit que c'est /
            if($_SERVER['REQUEST_URI'] === $_SERVER['SCRIPT_NAME']){
                $baseUrl .= trim($_SERVER['SCRIPT_NAME'],'/') . '/';
            }
        }


        $this->setBaseUrl($baseUrl);
    }

    /**
     * Init l'environnement
     */
    //TODO: faire une classe request
    private function initEnvironment(){
        $env = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_FILENAME);
        if($env == 'index'){
            $env = 'prod';
        }

        $this->setEnvironment(new \Qwik\Kernel\Environment\Environment($env, $this));
    }

    /**
     * @return string retourne la string dont il faut trouvé la route
     */
    //TODO: faire avec getEnv, une classe request
    private function getUri(){

        //PATH_INFO, c'est quand on écrit par exemple dev.php/fr/home. On a /fr/home dans PATH_INFO.
        if(isset($_SERVER['PATH_INFO'])){
            return $_SERVER['PATH_INFO'];
        }else{
            //Si on a juste demandé /index.php ou /dev.php, alors on dit que c'est /
            if($_SERVER['REQUEST_URI'] === $_SERVER['SCRIPT_NAME']){
                return '/';
            }
        }
        //REQUEST_URI, c'est quand on écrit directement /fr/home
        return $_SERVER['REQUEST_URI'];
    }
    /**
     * Affiche la page demandée
     */
    public function render(){

        $uri = $this->getUri();

        try{
            $response = $this->getRouterManager()->getResponseForUri($uri);
        }catch(\Qwik\Kernel\App\Page\PageNotFoundException $ex){

            //Récupère le fichier s'il existe. Ceci peut arriver lorsqu'on demande un fichier statitque (js, css, jpg, etc...) alors qu'on a le prefix dev.php par exemple
            $response = $this->getResponseOfFile($uri);
            //Si on a toujours pas de réponse
            if($response === null){
                $response = $this->getResponseForException($ex);
            }
        }catch (\Exception $ex){ //Si j'ai une exception, je la catch, pour joliment l'afficher
            $response = $this->getResponseForException($ex);
        }
        //Affichage de la Response
        $response->render();
	}

    /**
     * @param \Exception $ex
     * @return Routing\Response
     */
    private function getResponseForException(\Exception $ex){
        $pageManager = new \Qwik\Kernel\App\Page\PageManager();
        //Je vais donc chercher une page en fonction de l'erreur
        $page = $pageManager->findErrorBySite($this->getSite(), $ex);

        //Contenu "Error" par défaut
        $content = 'Error ' . $ex->getCode();
        //Si c'est pas null, alors on va renderer
        if(!is_null($page)){
            $content = TemplateProxy::getInstance()->renderPage($page);
        }else{
            //Log du message d'erreur
            \Qwik\Kernel\Log\Logger::getInstance()->warning('<h1>Error '.$ex->getCode(). '</h1>' . $ex->getMessage().' ('.$ex->getFile().'. Line:'.$ex->getLine() .')');
        }

        //On fait une Response avec le contenu récupéré
        $response = new \Qwik\Kernel\App\Routing\Response();
        $response->setContent($content);

        return $response;
    }
    /**
     * @param $uri
     * @return null|Routing\Response
     */
    private function getResponseOfFile($uri){

        $virtualPath = '/' . $this->getSite()->getVirtualUploadPath();
        //Si on est ici, on demande une ressource dans le dossier "public"
        if(strpos($uri, $virtualPath) === 0){ // Le fichier demandé est "url rewrité"
            $uri = DIRECTORY_SEPARATOR . $this->getSite()->getRealUploadPath() . substr($uri, strlen($virtualPath));
        }
        //On rajoute à l'url, le path vers www
        $fullPathOfFile = $this->getSite()->getWww() . $uri ;
        $fullPathOfFile = str_replace('/', DIRECTORY_SEPARATOR, $fullPathOfFile);

        switch(strtolower(pathinfo($uri, PATHINFO_EXTENSION))){
            //Liste des extensions autorisées à être lues (switch plus rapide que in_array)
            case 'js':
            case 'css':
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'png':
            case 'txt':
            case 'html':
            case 'htm':
            case 'doc':
            case 'docx':
            case 'xls':
            case 'xlsx':
            case 'ppt':
            case 'pptx':
            case 'csv':
            case 'pdf':
                //Si le fichier existe, on va le chercher, sinon on passera au "default" qui renvoi null
                if(file_exists($fullPathOfFile)){
                    $response = new \Qwik\Kernel\App\Routing\Response();
                    $response->setContent(file_get_contents($fullPathOfFile));
                    $response->setFileName($fullPathOfFile);
                    return $response;
                }
                //break; #nobreak!
            default:
                return null;
        }
    }

    /**
     * Renvoi un module en fonction des paramètres
     * @param $pageUrl
     * @param $zoneName
     * @param $uniqId
     * @return Module
     * @throws \Exception
     */
    public function findModule($pageUrl, $zoneName, $uniqId){
		$page = $this->getSite()->getPage($pageUrl);
		if(is_null($page)){
			throw new \Exception('Page '.$pageUrl.' introuvable');
		}
		foreach($page->getZone($zoneName)->getModules() as $module){
			
			if($module->getUniqId() == $uniqId){
				return $module;
			}
		}
		throw new \Exception('Impossible de trouver le module');
	}

	
}