<?php

namespace Qwik\Kernel\App\Module\File;

abstract class File {
	
	private $config;
	
	
	public static function get($type, $config){
		$className = '\Qwik\Kernel\App\Module\File\\'.ucfirst($type);
		return new $className($config);
	}
	
	public function __construct($config){
		//Si c'est juste une string alors c'est le path 
		if(!is_array($config)){
			$config = array('path' => $config);
		}
        //On remplace %%locale%% par la langue. Au cas où on a un fichier différent en fonction de la langue du visiteur
        $config['path'] = str_replace('%%locale%%', \Qwik\Kernel\App\Language::get(), $config['path']);
		$this->config = $config;
	}
	
	public function getAttribute($key){
		return isset($this->config[$key]) ? $this->config[$key] : '';
	}
	
	public function getConfig(){
		return $this->config;
	}
	

	abstract public function __toString();
	
}