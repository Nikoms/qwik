<?php

namespace Qwik\Kernel\App\Module;

use Qwik\Kernel\App\Language;


abstract class Module {


	private $name;
	private $zone;
	private $config;
	private $configObject;
	private $uniqId;

    /**
     * @param $config
     * @param $zone
     * @param $uniqId
     * @return Module
     */
    public static function get($config, $zone, $uniqId){
        if(is_array($config)){
            return self::getWithArray($config, $zone, $uniqId);
        }
        return self::getWithFile((string) $config, $zone, $uniqId);
	}

    /**
     * Construction d'un module en fonction d'un array config
     * @param $config
     * @param $zone
     * @param $uniqId
     * @return Module
     * @throws \Exception
     */
    private function getWithArray(array $config, $zone, $uniqId){
        $name = isset($config['module']) ? $config['module'] : '';

        $className = self::getClassName($name);

        if(!class_exists($className)){
            throw new \Exception('Module '.$name.' not found');
        }

        $module = new $className();
        $module->setConfig($config['config']);
        $module->setName($name);
        $module->setZone($zone);
        $module->setUniqId($uniqId);
        return $module;
    }

    /**
     * Construction d'un module en fonction d'une string (path vers fichier dans "resources")
     * @param $filePath
     * @param $zone
     * @param $uniqId
     * @return Module
     * @throws \Exception
     */
    private function getWithFile($filePath, $zone, $uniqId){
        $file = $zone->getPage()->getSite()->getPath() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filePath);
        if(!file_exists($file)){
            throw new \Exception('File ' . $file . ' not found (for module creation)');
        }
        return self::getWithArray(\Symfony\Component\Yaml\Yaml::parse($file), $zone, $uniqId);
    }


	//Ajout de route (si nécessaire)
	public static function injectInApp($app, $site){

	}

	//Renvoit le nom de la classe que devrait avoir le module "$name"
	public static function getClassName($name){
		return '\Qwik\Kernel\Module\\' . ucfirst($name) . '\Entity\\' . ucfirst($name);
	}
    public static function getModulesPath(){
        return __DIR__ . '/../../Module';
    }

	public function __construct(){
		$this->config = array();
		//$this->getConfig();
	}

	public function setConfig($config){
		$this->config = $config;
	}
	public function getConfig(){
		return $this->config;
	}
	public function setZone($zone){
		$this->zone = $zone;
	}
	public function getZone(){
		return $this->zone;
	}

	public function setName($name){
		$this->name = $name;
	}
	public function getName(){
		return $this->name;
	}
	public function setUniqId($uniqId){
		$this->uniqId = $uniqId;
	}
	public function getUniqId(){
		return $this->uniqId;
	}

	public function getConfigObject(){
		if(is_null($this->configObject)){
			$class_info = new \ReflectionClass($this);
			$pathOfConfig = dirname($class_info->getFileName()) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config';
			$config = \Qwik\Kernel\App\Config::getInstance()->getConfig($pathOfConfig);
			$this->configObject = new Config($config);
		}
		return $this->configObject;
	}
    public function translate($key){
        return $this->getConfigObject()->get('translations.' . $key);
    }

	public function __toString(){
		try{
			return \Qwik\Kernel\App\TemplateProxy::getInstance()->renderModule($this);
		}catch(\Exception $ex){
			return $ex->getMessage();
		}
	}

	public function getTemplatePath(){
		return ucfirst($this->getName()) . '/views/display.html.twig';
	}

	public function getTemplateVars(){
		return $this->getConfig();
	}



}