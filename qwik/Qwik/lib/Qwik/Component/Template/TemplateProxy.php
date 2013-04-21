<?php

namespace Qwik\Component\Template;

use Qwik\Cms\AppManager;
use Qwik\Cms\Module\Module;
use Qwik\Component\Locale\Language;

/**
 * Proxy vers un moteur de template. Dans notre cas Twig, mais ca peut changer facilement
 */
class TemplateProxy {

    /**
     * @var \Twig_Environment Moteur de template Twig
     */
    private $templateEngine;
    /**
     * @var TemplateProxy Singleton
     */
    private static $singleton;

    /**
     * Initialisation du moteur de template
     * @param AppManager $appManager
     */
    public static function init(AppManager $appManager){
        self::$singleton = new TemplateProxy($appManager);
    }


    /**
     * Récupération du Singleton
     * @return TemplateProxy
     * @throws \Exception
     */
    public static function getInstance(){
        if(is_null(self::$singleton)){
            throw new \Exception("Call init first");
        }
        return self::$singleton;
    }

    public function __construct(AppManager $appManager){

        //Chemins vers les twig
        $paths = AppManager::getInstance()->getEnvironment()->get('template.path');
        foreach($paths as $key => $path){
            $paths[$key] = str_replace('/', DIRECTORY_SEPARATOR, $path);
            //On a peut-être pas le dossier? (genre includes)
            if(!file_exists($path)){
                unset($paths[$key]);
            }
        }
        $loader = new \Twig_Loader_Filesystem($paths);

        //Création du moteur de template
        $twig = new \Twig_Environment($loader, array(
            //Si debug, pas de cache, sinon, ca se trouve dans le path du site
            'cache' => $appManager->getEnvironment()->get('template.cache', false),
            //Mode debug ou pas (voir doc), pour avoir un __toString
            'debug' => $appManager->getEnvironment()->get('template.debug', false),
            //On est strict quand on debug, sinon pas
            'strict_variables' => $appManager->getEnvironment()->get('template.strict', false),
            //On auto escape pas les vars, on le fera quand on en aura besoin
            'autoescape' => false,
        ));

        //Et hop on set le template engine
        $this->setTemplateEngine($twig);

        //Ajout de extensions
        $this->addExtensions();

    }

    /**
     * @return \Twig_Environment
     */
    public function getTemplateEngine(){
        return $this->templateEngine;
    }

    /**
     * Suppression du cache (s'il y en a)
     */
    public function clearCache(){
        $this->getTemplateEngine()->clearCacheFiles();
    }

    /**
     * Renvoi l'affichage d'une page
     * @param \Qwik\Cms\Page\Page $page
     * @return string
     */
    public function renderPage(\Qwik\Cms\Page\Page $page){
        return $this->getTemplateEngine()->render(
            'templates/' . $page->getTemplate() . '/display.html.twig',
            array(
                'page' => $page
            )
        );
    }

    /**
     * Renvoi l'affichage d'un module
     * @param $module
     * @return string
     */
    public function renderModule(Module $module){
        //Dans les variables du template, on rajoute toujours "module"
        return $this->getTemplateEngine()->render(
            $module->getTemplatePath(),
            array_merge(
                $module->getTemplateVars(),
                array('module' => $module)
            )

        );
    }

    /**
     * Renvoi la string compilé avec le template et les variables
     * @return string
     * @param string $templatePath
     * @param array $vars
     */
    public function renderTemplate($templatePath, $vars = array()){
        return $this->getTemplateEngine()->render((string) $templatePath, $vars);
    }




    /**
     * @param \Twig_Environment $templateEngine
     */
    private function setTemplateEngine(\Twig_Environment $templateEngine){
        $this->templateEngine = $templateEngine;
    }

    /**
     * Ajout des extension dans le moteur de template
     * @param AppManager $appManager
     */
    private function addExtensions(){
        //Ajout de la méthode pour traduire un truc dans le template
        $this->getTemplateEngine()->addFilter('translate', new \Twig_Filter_Function('\Qwik\Component\Locale\Language::getValue'));
        //Renvoi la langue en cours
        $this->getTemplateEngine()->addFunction('locale', new \Twig_Function_Function('\Qwik\Component\Locale\Language::get'));
        //Gestion des path
        $this->getTemplateEngine()->addFunction('path', new \Twig_Function_Function('\Qwik\Component\Template\path'));
        //Renvoi l'asset
        $this->getTemplateEngine()->addFunction('asset', new \Twig_Function_Function('\Qwik\Component\Template\asset'));
    }

}


/**
 * Renvoi le path de l'asset. Pour le moment, peu importe qu'on soit en dev/prod, on renvoi toujours l'url "directe"
 * @param $uri
 * @return string
 */
function asset($uri){
	$allPathInfo = pathinfo(\Qwik\Cms\AppManager::getInstance()->getBaseUrl());
    //Remplacement des / + je ne veux pas de / à la fin, car uri commence par /
	$prePath = rtrim(str_replace('\\', '/', $allPathInfo['dirname']),'/');

	//Si basename est rempli et que basename == fileName alors c'est que notre uri était un dossier :)
	if($allPathInfo['basename'] !== '' && $allPathInfo['basename'] === $allPathInfo['filename']){
		$prePath .= '/' . $allPathInfo['basename'];
	}

    return $prePath . $uri;
}


function path($routeName, array $vars = array()){
    return AppManager::getInstance()->getRouter()->getPath($routeName,$vars);
}