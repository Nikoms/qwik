<?php

namespace Qwik\Kernel\App;

use Symfony\Component\Yaml\Yaml;

class Config {

	private static $instance;
	
// 	private $config = array();
	

	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Config();
		}
		return self::$instance;
	}
	
	public function getConfig($path){
		$config = array();
		$this->loadPath($path, $config);
		return $config;
	}
	
	//Sortie dans $config
	private function loadPath($path, &$config){
		if(!is_dir($path)){
			return false;
		}
		if ($handle = opendir($path)) {

			/* This is the correct way to loop over the directory. */
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					$this->loadFile($path . '/' . $entry, $config);
				}
			}

			closedir($handle);
			return true;
		}
		return false;
	}

	//Sortie dans $config
	private function loadFile($filePath, &$config){
		if(!file_exists($filePath)){
			return false;
		}
		$name = pathinfo($filePath, PATHINFO_FILENAME);
		$config[$name] = Yaml::parse($filePath);
		return true;
	}
	
// 	public function get($name, $needed = ''){
	
// 		if(empty($this->config[$name])){
// 			return array();
// 		}
// 		if(empty($needed)){
// 			return $this->config[$name];
// 		}
// 		//On va parcourir ce qu'on demande
// 		$needed = explode('.', $needed);
// 		$tmp = $this->config[$name];
// 		foreach($needed as $need){
// 			if(is_null($tmp[$need])){
// 				return array();
// 			}
// 			$tmp = $tmp[$need];
// 		}
// 		return $tmp;
// 	}
	
	
}