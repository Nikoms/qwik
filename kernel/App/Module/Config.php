<?php

namespace Qwik\Kernel\App\Module;

use Qwik\Kernel\App\Language;
use Qwik\Kernel\App\Module\File\StaticFile;


/**
 * Classe de gestion de config. Un array est transformé en config, et peut-être attaqué comme un objet
 */
class Config {

    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config){
		$this->config = $config;
	}

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
		foreach($this->config['config']['files'][$type] as $fileConfig){
			$return[] = new StaticFile($fileConfig);
		}
		
		return $return;
	}

    /**
     * Retourne la valeur d'une élément de la config. Le path correspond à l'endroit où se trouve la valeur que l'on veut récupéré dans le fichier de config
     * Ex:
     * config.variable correspond à demander $this->config['config']['variable']
     * @param $path string
     * @param null $defaultValue
     * @return mixed
     */
    public function get($path, $defaultValue = null){
        $path = trim((string) $path);
        return $this->getValueOf(explode('.', $path), $this->getConfig(), $defaultValue);
    }


    /**
     * @return array
     */
    private function getConfig(){
        return $this->config;
    }

    /**
     * Récupération d'une valeur de la config en indiquant toutes les étapes (path) du tableau
     * Ex: array('config','variable') correspond à demander $this->config['config']['variable']
     * @param array $path
     * @param array $currentPosition
     * @param $defaultValue mixed valeur par défaut si on ne trouve pas la valeur
     * @return mixed
     */
    private function getValueOf(array $path, array $currentPosition, $defaultValue){
        //On prend (en enlevant) le premier élément du tableau path
        $keyNeeded = array_shift($path);
        //Si l'élément existe, bingo
        if(isset($currentPosition[$keyNeeded])){
            //Si y'a plus rien dans path, alors on renvoit la valeur, on a été jusqu'au bout :)
            if(empty($path)){
                return $currentPosition[$keyNeeded];
            }
            //On en a pas fini, on a encore du "path" sous la main, on va un niveau en dessous
            return $this->getValueOf($path, $currentPosition[$keyNeeded], $defaultValue);
        }
        //L'élément n'existe pas, on renvoit la valeur par défaut
        return $defaultValue;
    }
}