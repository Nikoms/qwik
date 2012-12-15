<?php

namespace Qwik\Kernel\App;

use Qwik\Kernel\App\Language;

class TemplateProxy {
	
	private $templateEngine;
    private static $singleton;

    public static function init(\Qwik\Kernel\App\AppManager $appManager){
    	$site = $appManager->getSite();
        self::$singleton = new TemplateProxy();

        require_once __DIR__ . '/../vendor/Twig/Autoloader.php';
        \Twig_Autoloader::register();

        
        $loader = new \Twig_Loader_Filesystem(array(
            $site->getPath() . '/resources',
            __DIR__ . '/../Module',
            __DIR__ . '/../Resources',
        ));
        //todo: activer cache en prod
        $twig = new \Twig_Environment($loader, array(
        	//Si debug, pas de cache, sinon, ca se trouve dans le path du site
            'cache' => $appManager->isDebug() ? false : $site->getPath() . '/cache',
        	//Mode debug ou pas (voir doc), pour avoir un __toString
            'debug' => $appManager->isDebug(),
        	//On est strict quand on debug, sinon pas
        	'strict_variables' => !$appManager->isDebug(),
        	//On auto escape pas les vars, on le fera quand on en aura besoin
            'autoescape' => false,
        ));
        //Ajout de la méthode pour traduire un truc dans le template
        $twig->addFilter('translate', new \Twig_Filter_Function('\Qwik\Kernel\App\Language::getValue'));
        //Renvoi la langue en cours
        $twig->addFunction('locale', new \Twig_Function_Function('\Qwik\Kernel\App\Language::get'));
        $twig->addGlobal('app', $appManager);

        //Et hop on set le template engine
        self::$singleton->setTemplateEngine($twig);

    }

    public static function getInstance(){
        if(is_null(self::$singleton)){
            throw new \Exception("Call init first");
        }
        return self::$singleton;
    }

    public function __construct(){

    }

	public function setTemplateEngine($templateEngine){
		$this->templateEngine = $templateEngine;
	}
    public function getTemplateEngine(){
        return $this->templateEngine;
    }

    public function clearCache(){
        $this->getTemplateEngine()->clearCacheFiles();
    }
    
	public function renderPage(\Qwik\Kernel\App\Page\Page $page){
		//Si on a pas de template, on affiche rien
		if($page->getTemplate() == ''){
			return '';
		}
		return $this->getTemplateEngine()->render('templates/' . $page->getTemplate() . '/display.html.twig', array(
			'page' => $page
		));
	}
	
	
	public function renderModule($module){
		return $this->getTemplateEngine()->render(
			$module->getTemplatePath(), 
			array_merge(
				$module->getTemplateVars(),
				array('module' => $module)
			)
				
		);
	}
}