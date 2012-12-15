<?php

namespace Qwik\Kernel\App\Module\File;

class File {
	
	private $config;

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

    public function getPath(){
        $config = $this->getConfig();
        return $config['path'];
    }

    /**
     * Utile pour le array_unique
     */
    public function __toString(){
        return $this->getPath();
    }

}