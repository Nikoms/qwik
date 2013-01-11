<?php

namespace Qwik\Kernel\App\Site;


//use Symfony\Component\Yaml\Yaml;
use Qwik\Kernel\App\Page\Page;



use Qwik\Kernel\App\Language;

/**
 * Classe qui représente un site
 */
class Site {

    /**
     * @var array Tableau de config
     */
    private $config;
    /**
     * @var string domaine représenté
     */
    private $domain;
    /**
     * @var string Chemin vers le dossier non-atteignable du site. Là où l'on va retrouver la config, les templates twig, etc...
     */
    private $path;
    /**
     * @var string path du www, c'est-à-dire là où se trouve l'index.php
     */
    private $www;

    /**
     *
     */
    public function __construct(){
	}


    /**
     * @return array Tableau de la config
     */
    public function getConfig(){
		if(is_null($this->config)){
			$this->initConfig();
		}
		return $this->config;
	}

    /**
     * @return string
     */
    public function getDomain(){
		return $this->domain;
	}

    /**
     * @param $domain
     */
    public function setDomain($domain){
		$this->domain = (string) $domain;
	}

    /**
     * @return string
     */
    public function getWww(){
		return $this->www;
	}

    /**
     * @param $www string
     */
    public function setWww($www){
		$this->www = (string) $www;
	}

    /**
     * @return string Chemin virtuel vers "getRealUploadPath". Ceci juste afin d'avoir un path plus beau qu'un path avec "denouveau" le nom de domaine
     */
    public function getVirtualUploadPath(){
        //TODO: Donner la possibilité de mettre ceci dans la config
        //q comme qwik!
        return 'q/';
    }

    /**
     * @return string Chemin où se trouve les fichiers publiques du site (upload, fichier css/js, etc...)
     */
    public function getRealUploadPath(){
        //TODO: Donner la possibilité de mettre ceci dans la config
        return 'pissette/'. $this->getDomain();
    }

    /**
     * @return string
     */
    public function getPath(){
		return $this->path;
	}

    /**
     * @param $path
     */
    public function setPath($path){
		$this->path = (string) $path;
	}

    /**
     * @return string Langue du site par défaut. Si rien n'est trouvé, on utilise le francais
     */
    public function getDefaultLanguage(){
		$languages = $this->getLanguages();
		return (count($languages) > 0) ? $languages[0] : 'fr';
	}

    /**
     * @return array Tableau des langues disponibles sur le site
     */
    public function getLanguages(){
		$config = $this->getConfig();
		return isset($config['general']['languages']['available']) ? $config['general']['languages']['available'] : array();
	}

    /**
     * @return string Récupère le titre du site dans la config du site. Vide si aucun titre n'a été trouvé
     */
    public function getTitle(){
        $config = $this->getConfig();
        return isset($config['general']['title']) ? Language::getValue($config['general']['title']) : '';
    }


    /**
     * @return \Qwik\Kernel\App\Page\Page[] Les pages du sites
     */
    public function getPages(){
        if(empty($this->pages)){
            $pageManager = new \Qwik\Kernel\App\Page\PageManager();
            $this->pages = $pageManager->findAll($this);
        }
		return $this->pages;
	}

    /**
     * @return bool Indique si le site existe. Il faut pour cela que le fichier "general" dans config existe
     */
    public function exists(){
		return isset($this->getConfig()['general']);
	}

    /**
     * @return bool Indique si le site est un alias d'un autre. Pour cela on vérifie juste si on a une redirection de prévue dans la config générale du site
     */
    public function isAlias(){
        return $this->getRedirect() !== '';
    }

    /**
     * @return string Renvoi quel est l'alias (redirection) du site. Renvoi vide si le site n'est pas un alias
     */
    public function getRedirect(){
        $config = $this->getConfig();
        return isset($config['general']['redirect']) ? $config['general']['redirect'] : '';
    }

    /**
     * Initialise la config
     * @return Site
     */
    private function initConfig(){
		$this->config = \Qwik\Kernel\App\Config::getInstance()->getConfig($this->getPath().'/config');
        return $this;
	}


    /**
     * @return string Renvoi quel est le code pour google analytics. Renvoi vide si aucun code n'est prévu
     */
    //TODO: devrait être ailleurs (utiliser le pattern Decorator? :))
    public function getGoogleAnalytics(){
		$config = $this->getConfig();
		return (isset($config['general']['google']) && isset($config['general']['google']['analytics'])) ? (string) $config['general']['google']['analytics'] : '';
	}
	
}