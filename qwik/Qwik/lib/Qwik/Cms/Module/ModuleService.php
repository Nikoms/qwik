<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 2/05/13
 * Time: 1:26
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Cms\Module;

use Qwik\Cms\Page\Page;
use Silex\Application;

class ModuleService {

    /**
     * @var \Silex\Application
     */
    private $app;

    /**
     * @var array
     */
    private $controllers;

    public function __construct(Application $app){
        $this->app = $app;
        $this->controllers = array();
    }

    /**
     * @param string $moduleName
     * @return mixed
     * @throws \Exception
     */
    public function getController($moduleName){

        if(isset($this->controllers[$moduleName])){
            return $this->controllers[$moduleName];
        }


        $modulePath = $this->app['env']->get('modules.' . $moduleName, false);
        if($modulePath === false){
            throw new \Exception('Module '.$moduleName.' not found');
        }

        $className = $modulePath . '\Controller';
        $this->controllers[$moduleName] = new $className($this->app);
        return $this->controllers[$moduleName];
    }

    public function render(Info $info){
        return $this->getController($info->getName())->render($info);
    }


    /**
     * @param Page $page
     * @param $type
     * @return array
     */
    public function getAssets(Page $page, $type){
        $files = array();
        foreach($page->getZones() as $zone){
            foreach($zone->getModules() as $info){
                $controller = $this->getController($info->getName());
                $files = array_merge($files, $controller->getConfig()->getAssets($type));
            }
        }
        return array_unique($files);
    }
}