<?php

namespace Qwik\Kernel\App\Zone;

use Qwik\Kernel\App\Module;

class Zone {

	private $config;
	private $page;
	private $modules;
	private $name;
	
	public function __construct(){
		$this->config = array();
	}
	
	public function setConfig($config){
		$this->config = $config;
	}
	
	public function getConfig(){
		return $this->config;
	}
	public function setPage($page){
		$this->page = $page;
	}
	
	public function getPage(){
		return $this->page;
	}
	

	public function setName($name){
		$this->name = $name;
	}
	public function getName(){
		return $this->name;
	}
	
	
	public function getUrl(){
		$config = $this->getConfig();
		return $config['url'];
	}
	
	public function getModuleClass($moduleName){
		return \Qwik\Kernel\App\Module\Module::get($moduleName);
	}
	
	public function getModules(){
		if(is_null($this->modules)){
			$configs = $this->getConfig();
		
			$this->modules = array();
			foreach($configs as $key => $config){
				try{
					$this->modules[] = $this->getBuildedModule($key, $config);
				}catch(\Exception $ex){
					continue;
				}
			}
		}
		
		return $this->modules;
	}
	
	private function getBuildedModule($key, $config){
		$module = $this->getModuleClass($config['module']);
		$module->setConfig($config['config']);
		$module->setName($config['module']);
		$module->setZone($this);
		$module->setUniqId($this->getName() . '_' . $key);
		return $module;
	}
	
	public function __toString(){
		return implode('', $this->getModules());
	}
	
	//Renvoit les fichiers statiques (js,css) nécessaire pour le bon affichage de la page. On demande simplement aux de la zone modules de la zone de bien vouloir donner leur fichiers et on fait le récap :)
	public function getFiles(){
		$files = array();
		$files['javascript'] = array();
		$files['css'] = array();
		foreach ($this->getModules() as $module){
			$files['javascript'] = array_merge($files['javascript'], $module->getConfigObject()->getFiles('javascript'));
			$files['css'] = array_merge($files['css'], $module->getConfigObject()->getFiles('css'));
		}
		
		$files['javascript'] = array_unique($files['javascript']);
		$files['css'] = array_unique($files['css']);
		return $files;
	}

}