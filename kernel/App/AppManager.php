<?php
namespace Qwik\Kernel\App;

use Qwik\Kernel\App\Language;
use Qwik\Kernel\App\Page\PageNotFoundException;
use Qwik\Kernel\Environment\Environment;
use Qwik\Kernel\Template\Asset;
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
     * @var \Qwik\Kernel\App\Routing\Router
     */
    private $router;
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
    public static function initWithPath($www){
        $domain = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);
        self::$instance = new AppManager();
        self::$instance->init($www, $domain);
        return self::$instance;
	}


    /**
     * Récupération du nom de domain cleané (sans dev. s'il y en avait un)
     * @param $domain Nom de domaine (peut avoir un dev.) devant
     * @return string
     */
    private function getProperDomain($domain){
		return (strpos($domain, 'local.') === 0) ? substr($domain, 6) : $domain;
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
    private function __construct(){

	}

    /**
     * Initialise l'app avec le www et le nom de domaine
     * @param string $www
     * @param string $domain
     */
    public function init($www, $domain){

        $www = (string) $www;
        $domain = (string) $domain;

        //Init base url
        $this->initBaseUrl();

        //Set de l'environnement
        $this->initEnvironment();


        //On récupère le bon domaine
        $domain = $this->getProperDomain($domain);

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

        //Initialisation du router
        $this->router = new \Qwik\Kernel\App\Routing\Router($this->getBaseUrl());

        //Initialise le moteur de template
        TemplateProxy::init($this);

        //Initialise les routes de de base
        $this->initRoutes();
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
        $this->getRouter()->get('root', '/', function() use ($site){

            //Récupération de la première page
            $pageManager = new \Qwik\Kernel\App\Page\PageManager();
            $firstPage = $pageManager->findFirst($site);

            //Si pas de page, alors 404
            if(!$firstPage){
                throw new PageNotFoundException();
            }

            //TODO: faire un ResponseRedirect
            //TODO: création d'une url avec un nom de route + variables (twig aussi)
            header('Location: ' . $this->getBaseUrl() . '/' . Language::get() . '/' . $firstPage->getUrl());
            exit();
        });

        //J'ai choisi une langue, mais pas de page, j'attends une redirection vers la première page
        //TODO: Code dupliqué par rapport au "/". Faire quelque chose
        $this->getRouter()->get('root_language', '/{_locale}', function($_locale) use($site){

            //Si on gère la langue, alors on va sur la première page
            if(in_array($_locale, $site->getLanguages())){
                $pageManager = new \Qwik\Kernel\App\Page\PageManager();
                $firstPage = $pageManager->findFirst($site);

                //Si pas de "première" page, alors Exception!
                if(!$firstPage){
                    throw new PageNotFoundException();
                }
                //Response redirect
                //TODO: faire un ResponseRedirect
                //TODO: création d'une url avec un nom de route + variables (twig aussi)
                header('Location: ' . $this->getBaseUrl() . '/' . Language::get() . '/' . $firstPage->getUrl());
                exit();

            }else{ //Sinon Exception :)
                throw new PageNotFoundException();
            }
        })->assert('_locale','[a-z]{2}');

        //J'ai une langue et une page :)
        $this->getRouter()->get('page', '/{_locale}/{pageName}', function($_locale, $pageName) use($site){
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
                throw new PageNotFoundException();
            }
            return TemplateProxy::getInstance()->renderPage($page);

        })->assert('_locale','[a-z]{2}');

        //Clear varnish et les templates
        $this->getRouter()->get('cc', '/admin/cc', function() use($site){

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
                throw new PageNotFoundException();
            }
            return TemplateProxy::getInstance()->renderPage($page);
        });

        //On va voir si les modules on des routes
		$this->addModulesRoutes();
	}

    /**
     * @return Routing\Router
     */
    public function getRouter(){
        return $this->router;
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
        //on enlève le slash au début et à la fin, comme ca, c'est bon pour tout le monde qu'on soit dans le / ou /dd/lol/ok
        $baseUrl = trim($baseUrl, '/');


        if(
            isset($_SERVER['PATH_INFO']) //Si path_info, alors on a fait un index.php/mon/path... ou dev.php/mon/path...
            || ($this->getCleanedUri() === $_SERVER['SCRIPT_NAME']) //Si on a juste tapé /dev.php ou /index.php par exemple
        ){
            $baseUrl .= '/' . basename($_SERVER['SCRIPT_NAME']);
        }

        $this->setBaseUrl($baseUrl);
    }

    /**
     * Renvoi la requestUri sans la querystring et autres params
     * @return string
     */
    private function getCleanedUri(){
        return substr($_SERVER['REQUEST_URI'], 0, strcspn($_SERVER['REQUEST_URI'], '?#'));
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

        //On enlève les querystring et autre de la request URI
        $uri = $this->getCleanedUri();

        //PATH_INFO, c'est quand on écrit par exemple dev.php/fr/home. On a /fr/home dans PATH_INFO.
        if(isset($_SERVER['PATH_INFO'])){
            $uri = $_SERVER['PATH_INFO'];
        }else{
            //Si on a juste demandé /index.php ou /dev.php, alors on dit que c'est /
            if($uri === $_SERVER['SCRIPT_NAME']){
                $uri = '/';
            }
        }


        return $uri;
    }
    /**
     * Affiche la page demandée
     */
    public function render(){

        $uri = $this->getUri();

        try{
            $response = $this->getRouter()->getResponseForUri($uri);
        /*}catch (PageNotFoundException $ex){
            //Si page not found, alors on va voir si on a pas un asset à cet endroit...
            $response = Asset::getResponseOfAsset($uri);
            //On a pas trouvé d'asset.. Bon bah tt pis :)
            if($response === null){
                $response = $this->getResponseForException($ex);
            }*/
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