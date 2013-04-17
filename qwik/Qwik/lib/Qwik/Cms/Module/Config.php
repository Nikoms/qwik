<?php

namespace Qwik\Cms\Module;


use Qwik\Component\Template\Asset;

/**
 * Classe de gestion de config. Un array est transformé en config, et peut-être attaqué comme un objet
 */
class Config extends \Qwik\Component\Config\Config{

    /**
     * Retourne un tableau de fichiers de type "$type"
     * @param $type
     * @return array
     */
    public function getFiles($type){
        $config = $this->getConfig();
		if(empty($config['config']) || empty($config['config']['files']) || empty($config['config']['files'][$type])){
			return array();
		}
		
		$return = array();
		foreach($config['config']['files'][$type] as $fileConfig){
			$return[] = new Asset($fileConfig);
		}
		
		return $return;
	}
}