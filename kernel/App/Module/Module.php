<?php

namespace Qwik\Kernel\App\Module;

use Qwik\Kernel\App\Language;


abstract class Module {
	

	private $name;
	private $zone;
	private $config;
	private $configObject;
	private $uniqId;
	
	//Renvoit un objet "$name" qui est en fait un Module
	public static function get($name){
		$className = self::getClassName($name);

		if(class_exists($className)){
			return new $className();
		}
		
		throw new \Exception('Module '.$name.' not found');
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