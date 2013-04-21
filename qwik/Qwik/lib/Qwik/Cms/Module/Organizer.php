<?php

namespace Qwik\Cms\Module;

use Qwik\Cms\AppManager;
use Qwik\Cms\Zone\Zone;
use Qwik\Component\Config\Config;
use Qwik\Component\Log\Logger;
use Symfony\Component\Yaml\Yaml;

class Organizer{

    public function __construct(){

    }


    /**
     * Construction d'un module en fonction d'un array config
     * @param array $config
     * @param Zone $zone
     * @param $uniqId
     * @return Module
     * @throws \Exception
     */
    static private function getWithArray(array $config, Zone $zone, $uniqId){

        if(empty($config['module'])){
            throw new \Exception("Module empty!");
        }

        //Récupération du nom de la classe (avec namespace)
        $className = self::getClassName($config['module']);

        if(!class_exists($className)){
            throw new \Exception('Module '.$config['module'].' not found');
        }

        /**
         * @var $module Module
         */
        $module = new $className();
        $module->setConfig(new Config($config['config']));
        $module->setZone($zone);
        $module->setUniqId($uniqId);
        return $module;
    }

    /**
     * Construction d'un module en fonction d'une string (path vers fichier dans "resources")
     * @param $filePath
     * @param \Qwik\Cms\Zone\Zone $zone
     * @param $uniqId
     * @return Module
     * @throws \Exception
     */
    static private function getWithFile($filePath, Zone $zone, $uniqId){
        //On prend la config du dossier config
        $file = $zone->getPage()->getSite()->getPath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filePath);

        if(!file_exists($file)){
            throw new \Exception('File ' . $file . ' not found (for module creation)');
        }
        return self::getWithArray(Yaml::parse($file), $zone, $uniqId);
    }

    /**
     * Récupération d'un module en fonction de sa config
     * @param $config array|string path vers la config ou array de config
     * @param Zone $zone
     * @param $uniqId string
     * @return Module
     */
    public static function get($config, Zone $zone, $uniqId){
        if(is_array($config)){
            return self::getWithArray($config, $zone, $uniqId);
        }
        return self::getWithFile((string) $config, $zone, $uniqId);
    }

    /**
     * Renvoi le nom de la classe (avec namespace) du module $name
     * @param $name string
     * @return string
     */
    public static function getClassName($name){
        return AppManager::getInstance()->getEnvironment()->get('modules.' . $name) . '\\' . ucfirst($name);
    }
}