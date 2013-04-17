<?php

namespace Qwik\Component\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * Classe qui permet de loader des config's Yaml dans un dossier, les transforment en array, et renvoi un array de config
 */
class Loader {

    /**
     * @var Config Singleton
     */
    private static $instance;

    /**
     * @return Loader
     */
    public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Loader();
		}
		return self::$instance;
	}

    /**
     * Récupération d'un array de config se trouvant dans le dossier $path
     * @param string $path Chemin où se trouve les fichiers config
     * @return array Tableau de config (array)
     */
    public function getPathConfig($path){
		return $this->loadPath($path);
	}

    /**
     * @param string $path chemin vers le fichier
     * @return array
     * @throws \Exception
     */
    public function getFileConfig($path){
        if(!file_exists($path)){
            throw new \Exception("File ".$path . " does not exists");
        }
        $config = $this->loadFile($path);
        return is_null($config) ? array() : $config;
    }

    /**
     * Rempli, dans $config,
     * @param string $path Chemin du dossier de configs
     * @return bool
     */
    private function loadPath($path){
        $path = (string) $path;
		if(!is_dir($path)){
			return array();
		}

        $configs = array();

        if ($handle = opendir($path)) {
			/* This is the correct way to loop over the directory. */
			while (false !== ($entry = readdir($handle))) {
                //On prend pas . et .. of course
				if ($entry != "." && $entry != "..") {
                    //On rajoute dans un array la config
                    $filePath = $path . '/' . $entry;
                    //La clé est le nom du fichier sans l'extension
                    $name = pathinfo($filePath, PATHINFO_FILENAME);
					$configs[$name] = $this->loadFile($filePath);
				}
			}
			closedir($handle);
			return $configs;
		}
		return array();
	}

    /**
     * Récupère un Yml pour le transformer en array
     * @param string $filePath Chemin vers le fichier Yaml
     * @return array
     */

    private function loadFile($filePath){
		return Yaml::parse($filePath);
	}

}