<?php

namespace Qwik\Kernel\App\Module;

use Qwik\Kernel\App\Module\File\StaticFile;


/**
 * Classe de gestion de config. Un array est transformé en config, et peut-être attaqué comme un objet
 */
class Config extends \Qwik\kernel\Config\Config{

    /**
     * Retourne un tableau de fichiers de type "$type"
     * @param $type
     * @return array
     */
    public function getFiles($type){
		if(empty($this->getConfig()['config']) || empty($this->getConfig()['config']['files']) || empty($this->getConfig()['config']['files'][$type])){
			return array();
		}
		
		$return = array();
		foreach($this->getConfig()['config']['files'][$type] as $fileConfig){
			$return[] = new StaticFile($fileConfig);
		}
		
		return $return;
	}
}