<?php

namespace Qwik\Kernel\App;

use Qwik\Kernel\App\Language;

class TemplateProxy {
	
	private $templateEngine;
    private static $singleton;

    public static function init($site){
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
            //'cache' => $site->getWww() . DIRECTORY_SEPARATOR . 'pissette' . DIRECTORY_SEPARATOR . '_twig' . DIRECTORY_SEPARATOR .'cache',
            'debug' => true,
            'autoescape' => false,
        ));
        $twig->addFilter('translate', new \Twig_Filter_Function('\Qwik\Kernel\App\Language::getValue'));
        $twig->addGlobal('locale', \Qwik\Kernel\App\Language::get());

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

	public function renderPage($page){
		if($page->getTemplate() == ''){
			return '';
		}
		return $this->getTemplateEngine()->render('templates/' . $page->getTemplate() . '/display.html.twig', array(
			'page' => $page,
			'site' => $page->getSite(),
			'locale' => Language::get()
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