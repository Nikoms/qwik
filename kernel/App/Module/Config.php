<?php

namespace Qwik\Kernel\App\Module;

use Qwik\Kernel\App\Language;
use Qwik\Kernel\App\Module\File\File;


class Config {
	
	private $config;
	
	public function __construct(array $config){
		$this->config = $config;
	}

	public function getFiles($type){
		if(empty($this->config['config']) || empty($this->config['config']['files']) || empty($this->config['config']['files'][$type])){
			return array();
		}
		
		$return = array();
		foreach($this->config['config']['files'][$type] as $fileConfig){
			$return[] = new File($fileConfig);
		}
		
		return $return;
	}
    public function get($path, $defaultValue = null){
        return $this->getValueOf(explode('.', $path), $this->config, $defaultValue);
    }

    private function getValueOf(array $path, $currentPosition, $defaultValue){
        $keyNeeded = array_shift($path);
        if($currentPosition[$keyNeeded]){
            //Si y'a plus rien dans path, alors on renvoit la valeur, on a été jusqu'au bout :)
            if(empty($path)){
                return $currentPosition[$keyNeeded];
            }
            return $this->getValueOf($path, $currentPosition[$keyNeeded], $defaultValue);
        }
        return $defaultValue;
    }
}