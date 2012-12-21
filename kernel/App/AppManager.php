<?php
namespace Qwik\Kernel\App;

use Qwik\Kernel\App\Language;
use Qwik\Kernel\App\TemplateProxy;

class AppManager {
	
	private static $instance;
	

	private $site;
	private $routerManager;
	private $isDebug;
    private $baseUrl;
	
	public static function getInstance(){
		if(is_null(self::$instance)){
			throw new \Exception('You must call init first!');
		}
		return self::$instance;
	}
	
	
	public static function init($www, $domain){
        \Qwik\Kernel\Log\Logger::getInstance();
//        try{
            self::$instance = new AppManager($www, $domain);
            return self::$instance;
//        }catch(\Exception $ex){
//            echo 'error';
//        }
	}
	
	private function initDebug($domain){
		//On est en debug si notre domaine commence par dev.
		$this->isDebug = strpos($domain, 'dev.') === 0;	
	}
	public function isDebug(){
		return $this->isDebug;
	}
	//Si on est en mode dev, on enleve dev. devant :)
	private function getProperDomain($domain){
		return $this->isDebug() ? substr($domain, 4) : $domain;
	}
	
	private function __construct($www, $domain){
		
		$this->initDebug($domain);
		$domain = $this->getProperDomain($domain);
		
		$siteManager = new \Qwik\Kernel\App\Site\SiteManager();
		$this->site = $siteManager->getByPath($www, $domain);
        //Si c'est un alias, alors on va rediriger  via un header
        if($this->getSite()->isAlias()){
            $redirectDomain = $this->getSite()->getRedirect();
            header('Location: http://'.$redirectDomain . $_SERVER['REDIRECT_URL']);
            exit();
            //Problème si on fait juste ca, on aura notre url rewriting qui va foirer (à moins de faire de liens symboliques lors de la création du nom de domaine)
            //$this->site = $siteManager->getByPath($www, $domain);
        }
        //Si le site existe pas, alors on va prendre default pour afficher une page standard
		if(!$this->getSite()->exists()){
			$this->site = $siteManager->getByPath($www, 'default');
		}
		//Pratique, on retient le baseUrl, pour pouvoir l'utiliser comme préfix. Du coup, on peut mettre l'appli où on veut!
        $baseUrl = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_NAME']));

		//Si c'est pas juste un slash, alors c'est par exemple /monDossier/ok. Il faut donc rajouter un slash au bout
		if($baseUrl !== '/'){
			$baseUrl = $baseUrl . '/';
		}



		$this->setBaseUrl($baseUrl);
		//Initialisation de la langue en cours
		Language::init($this->getSite()->getLanguages());
		$this->initApp();
	}

    private function setBaseUrl($baseUrl){
        $this->baseUrl = $baseUrl;
    }

    public function getBaseUrl(){
        return $this->baseUrl;
    }

	public function getSite(){
		return $this->site;
	}


	
	//Initialiste l'app
	private function initApp(){
		
		$this->routerManager = new \Qwik\Kernel\App\Routing\RouterManager();

		$this->initTemplateEngine();
		$this->initRoutes();
	}
	
	//Initialise le moteur de template
	private function initTemplateEngine(){
        TemplateProxy::init($this);
        //Si dans le GET il y a un clear, alors on clear le cache du template engine
        if(!empty($_GET['clear'])){
        	TemplateProxy::getInstance()->clearCache();
        }
	}

	//Ajoute les routes des modules
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
	
	//Initialise les routes
	private function initRoutes(){
		$site = $this->getSite();


		//Arrivée sur le site
        $this->getRouterManager()->get('base', '/', function() use ($site){
            $response = new \Qwik\Kernel\App\Routing\Response();
            $response->setTargetUrl(Language::get() . '/' . $site->getFirstPage()->getUrl());
            return $response;
        });
        
        //J'ai choisi une langue, mais pas de page
        $this->getRouterManager()->get('base', '/{_locale}', function($_locale) use($site){
            $response = new \Qwik\Kernel\App\Routing\Response();

            //Si on gère la langue, alors on va sur la première page
            if(in_array($_locale, $site->getLanguages())){
                $response->setTargetUrl($_locale . '/' . $site->getFirstPage()->getUrl());
            }else{ //Sinon Exception :)
                throw new \Qwik\Kernel\App\Page\PageNotFoundException();
            }
            return $response;
        });

        //J'ai une langue et une page :)
        $this->getRouterManager()->get('base', '/{_locale}/{pageName}', function($_locale, $pageName) use($site){
            //Changement de la langue quand c'est possible...
            Language::changeIfPossible($_locale);
            $page = $site->getPage($pageName);
            //Si pas de page, alors 404
            if(!$page){
                throw new \Qwik\Kernel\App\Page\PageNotFoundException();
            }
            return \Qwik\Kernel\App\TemplateProxy::getInstance()->renderPage($page);
        });
        
        //On va voir si les modules on des routes
		$this->addModulesRoutes();
	}

    public function getRouterManager(){
        return $this->routerManager;
    }

	
	//Affiche la page demandée
	public function render(){
        try{
            $response = $this->routerManager->getResponseForUri($_SERVER['REQUEST_URI']);
        }catch (\Exception $ex){
            $response = new \Qwik\Kernel\App\Routing\Response();
            $response->setContent($this->getErrorPage($ex, $_SERVER['REQUEST_URI']));
        }
        echo $response->getContent();
	}

    private function getErrorPage(\Exception $ex, $uri){
    	$page = $this->getSite()->getError($ex, $uri);
    	//Si c'est pas null, alors on va renderer
    	if(!is_null($page)){
        	return \Qwik\Kernel\App\TemplateProxy::getInstance()->renderPage($page);
    	}
    	return '';
    }
	
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