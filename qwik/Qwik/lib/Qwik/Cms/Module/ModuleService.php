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
use Qwik\Cms\Zone\Zone;
use Qwik\Component\Config\Config;
use Qwik\Component\Log\Logger;
use Silex\Application;
use Symfony\Component\Yaml\Yaml;

class ModuleService
{

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

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->controllers = array();
        $this->modules = array();
    }

    /**
     * Register tous les providers des modules
     */
    public function registerProviders()
    {
        $this->app->register(new RenderProvider());
        foreach ($this->app['qwik.modules'] as $moduleConfig) {
            $modulePath = $moduleConfig['path'];
            $className = $modulePath . '\ModuleProvider';
            $this->app->register(new $className());
        }
    }

    /**
     * @param $moduleName
     * @return mixed
     */
    public function getServiceProviderModule($moduleName)
    {
        return $this->app['qwik.module.' . $moduleName];
    }

    /**
     * @param string $moduleName
     * @return mixed
     * @throws \Exception
     */
    public function getController($moduleName)
    {

        if (isset($this->controllers[$moduleName])) {
            return $this->controllers[$moduleName];
        }

        if (!isset($this->app['qwik.modules'][$moduleName])) {
            throw new \Exception('Module ' . $moduleName . ' not found');
        }

        $modulePath = $this->app['qwik.modules'][$moduleName]['path'];

        $className = $modulePath . '\Controller';
        $this->controllers[$moduleName] = new $className();
        return $this->controllers[$moduleName];
    }

    /**
     * @param Info $info
     * @return string
     */
    public function render(Info $info)
    {
        return $this->app['qwik.module.render']->render($this->getServiceProviderModule($info->getName()), $info);
    }


    /**
     * @param Page $page
     * @param $type
     * @return AssetCollection
     */
    public function getAssets(Page $page, $type)
    {
        $assetCollection = new AssetCollection();
        foreach ($page->getZones() as $zone) {
            foreach ($this->getByZone($zone) as $info) {
                foreach ($this->getServiceProviderModule($info->getName())->getAssets($type) as $asset) {
                    $assetCollection->add($asset);
                }
            }
        }
        return $assetCollection;
    }


    /**
     * @param Zone $zone
     * @return Info[]
     */
    public function getByZone(Zone $zone)
    {
        $infos = array();

        foreach ($zone->getConfig() as $key => $config) {
            try {
                $info = new Info();
                //Si c'est pas un array alors, c'est une string qui mène vers le yml de la config
                if (!is_array($config)) {
                    $filePath =  str_replace('/', DIRECTORY_SEPARATOR, $this->app['qwik.path']['site']['structure'] . $config);
                    $config = Yaml::parse($filePath);
                }
                $info->setConfig(new Config($config));
                //$loader = new Loader();
                //$allConfig = $loader->getFileConfig($this->getConfigPath() . 'config.yml');
                $info->setZone($zone);
                //Le nom du module est un cast entre le nom de la zone + _ + la clé du module. Ceci afin que chaque module soit unique
                $info->setUniqId($zone->getName() . '_' . $key);
                $infos[] = $info;
            } catch (\Exception $ex) {
                Logger::getInstance()->error($ex->getMessage(), $ex);
                //Si on a une exception, on va au suivant
                continue;
            }
        }
        return $infos;
    }



    /**
     * Renvoi un module en fonction des paramètres
     * @param $pageUrl
     * @param $zoneName
     * @param $uniqId
     * @return Info
     * @throws \Exception
     */
    public function findModule($pageUrl, $zoneName, $uniqId)
    {
        $page = $this->app['qwik.page.service']->getOneByUrl($pageUrl);
        if (is_null($page)) {
            throw new \Exception('Page ' . $pageUrl . ' introuvable');
        }
        foreach ($this->getByZone($page->getZone($zoneName)) as $module) {
            if ($module->getUniqId() == $uniqId) {
                return $module;
            }
        }
        throw new \Exception('Impossible de trouver le module');
    }
}