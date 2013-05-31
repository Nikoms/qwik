<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 2/05/13
 * Time: 1:26
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Cms\Module;

use Assetic\Asset\AssetCollection;
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
        $this->app->register(new RenderProvider());
        foreach(array_keys($this->getList()) as $moduleName){
            $modulePath = $this->app['qwik.env']->get('modules.' . $moduleName, false);
            if($modulePath === false){
                throw new \Exception('Module '.$moduleName.' not found');
            }
            $className = $modulePath . '\ModuleProvider';
            $this->app->register(new $className());
        }
    }

    /**
     * @param $moduleName
     * @return mixed
     */
    public function getServiceProviderModule($moduleName){
        return $this->app['qwik.module.'.$moduleName];
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
     * @param Info $info
     * @return string
     */
    public function render(Info $info){
        return $this->app['qwik.module.render']->render($this->getServiceProviderModule($info->getName()),$info);
    }


    /**
     * @param Page $page
     * @param $type
     * @return AssetCollection
     */
    public function getAssets(Page $page, $type){
        $assetCollection = new AssetCollection();
        foreach($page->getZones() as $zone){
            foreach($zone->getModules() as $info){
                foreach($this->getServiceProviderModule($info->getName())->getAssets($type) as $asset){
                    $assetCollection->add($asset);
                }
            }
        }
        return $assetCollection;
    }
}