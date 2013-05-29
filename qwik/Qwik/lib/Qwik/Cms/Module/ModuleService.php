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
use Symfony\Component\Yaml\Yaml;

class ModuleService {

    /**
     * @var \Silex\Application
     */
    private $app;

    /**
     * @var array
     */
    private $controllers;
    /**
     * @var array
     */
    private $modules;

    public function __construct(Application $app){
        $this->app = $app;
        $this->controllers = array();
        $this->modules = array();
    }

    public function getList(){
        return $this->app['qwik.env']->get('modules', array());
    }

    /**
     * Register tous les providers des modules
     */
    public function registerProviders(){
        foreach(array_keys($this->getList()) as $moduleName){
            $provider = $this->getProvider($moduleName);
            foreach($provider->getConfig()->get('config.register', array()) as $serviceProvider){
                $this->app->register(new $serviceProvider());
            }
        }
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


        $modulePath = $this->app['qwik.env']->get('modules.' . $moduleName, false);
        if($modulePath === false){
            throw new \Exception('Module '.$moduleName.' not found');
        }

        $className = $modulePath . '\Controller';
        $this->controllers[$moduleName] = new $className();
        return $this->controllers[$moduleName];
    }

    /**
     * @param string $moduleName
     * @return mixed
     * @throws \Exception
     */
    public function getProvider($moduleName){

        if(isset($this->modules[$moduleName])){
            return $this->modules[$moduleName];
        }


        $modulePath = $this->app['qwik.env']->get('modules.' . $moduleName, false);
        if($modulePath === false){
            throw new \Exception('Module '.$moduleName.' not found');
        }

        $className = $modulePath . '\ModuleProvider';
        $this->modules[$moduleName] = new $className($this->app);
        return $this->modules[$moduleName];
    }

    public function render(Info $info){
        return $this->getProvider($info->getName())->render($info);
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
                $provider = $this->getProvider($info->getName());
                $files = array_merge($files, $provider->getConfig()->getAssets($type));
            }
        }
        return array_unique($files);
    }
}