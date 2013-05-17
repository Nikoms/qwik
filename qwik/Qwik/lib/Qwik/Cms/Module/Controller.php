<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:30
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Cms\Module;


use Qwik\Component\Config\Loader;
use Silex\Application;

class Controller {

    /**
     * @var Application
     */
    private $application;

    public function __construct(Application $app){
        $this->setApplication($app);
    }

    /**
     * @param \Silex\Application $application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * @return \Silex\Application
     */
    public function getApplication()
    {
        return $this->application;
    }


    /**
     * @return ModuleConfig
     */
    public function getConfig(){
        //On va calculer le path de la config du module
        $pathOfConfig = $this->getDirName() . DIRECTORY_SEPARATOR . 'config';
        //Transformation de l'array en objet
        return new Config(Loader::getInstance()->getPathConfig($pathOfConfig));
    }


    /**
     * render the module
     * @param Info $info
     * @return string
     */
    public function render(Info $info){
        $app = $this->getApplication();
        return $app['twig']->render($this->getTemplatePath(), $this->getTemplateVars($info));
    }


    /**
     * Récupération du template
     * @return string
     */
    public function getTemplatePath(){
        return basename($this->getDirName()) . '/views/display.html.twig';
    }

    protected function getDirName(){
        $rc = new \ReflectionClass(get_class($this));
        return dirname($rc->getFileName());
    }

    /**
     * @param Info $info
     * @return array
     */
    protected function getTemplateVars(Info $info){
        $return = $info->getConfig()->get('config');
        $return['this'] = $this->getModule($info);
        return $return;
    }

    /**
     * @param $info
     * @return Module
     */
    protected function getModule(Info $info){
        return new Module($info);
    }

    /**
     * Injection d'url spécialisées
     */
    public function injectUrl(){

    }
}