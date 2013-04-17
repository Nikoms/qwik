<?php

namespace Qwik\Component\Template;

use Qwik\Component\Locale\Language;

/**
 * Les attributs sont pour le moment "path", mais c'est ouvert pour pouvoir rajouter des type de media pour les css ou des conditions (if &gt IE8,etc...)
 */
class Asset{

    /**
     * @var array
     */
    private $config;

    /**
     * @param $config string|array Soit une string qui correspond au path du fichier, soit un array, avec path, et d'autres attributs
     *
     */
    public function __construct($config){
        //Si c'est juste une string alors c'est le path
        if(!is_array($config)){
            $config = array('path' => $config);
        }
        //On remplace %%locale%% par la langue. Au cas où on a un fichier différent en fonction de la langue du visiteur. Ex: monFichier_%%locale%%.css
        $config['path'] = str_replace('%%locale%%', Language::get(), $config['path']);
        $this->config = $config;
    }

    /**
     * Renvoi l'attribut de la config. Vide si la valeur n'a pas été trouvée
     * @param $key
     * @return string
     */
    public function getAttribute($key){
        $config = $this->getConfig();
        return isset($config[$key]) ? $config[$key] : '';
    }

    /**
     * Renvoi la config du fichier
     * @return array
     */
    public function getConfig(){
        return $this->config;
    }

    /**
     * Renvoi le path du fichier
     * @return string
     */
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