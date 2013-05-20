<?php

namespace Qwik\Cms\Site;


//use Symfony\Component\Yaml\Yaml;
use Qwik\Cms\Page\Page;


use Qwik\Component\Config\Loader;
use Qwik\Component\Locale\Language;

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
        return 'pissette/' . $this->getDomain() . '/';
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
        return isset($config['general']['title']) ? $config['general']['title'] : '';
    }


    /**
     * @return \Qwik\Cms\Page\Page[] Les pages du sites
     */
    public function getPages(){
        if(empty($this->pages)){
            $pageManager = new \Qwik\Cms\Page\PageManager();
            $this->pages = $pageManager->findAll($this);
        }
		return $this->pages;
	}

    /**
     * @param $url
     * @return null|Page
     */
    public function getPage($url){
        $url = (string) $url;
        $pages = $this->getPages();
        return isset($pages[$url]) ? $pages[$url] : null;
    }

    /**
     * @return bool Indique si le site existe. Il faut pour cela que le fichier "general" dans config existe
     */
    public function exists(){
        $config = $this->getConfig();
		return isset($config['general']);
	}

    /**
     * @return bool Indique si le site est un alias d'un autre. Un alias = on reste sur le meme domaine mais on accède aux infos de l'autre
     */
    public function getAlias(){
        exit('redirect todo');
        $config = $this->getConfig();
        return isset($config['general']['alias']) ? $config['general']['alias'] : '';
    }

    /**
     * @return string Renvoi quel est la redirectiondu site. Renvoi vide si le site n'a pas de redirection
     */
    public function getRedirect(){
        exit('redirect todo');
        $config = $this->getConfig();
        return isset($config['general']['redirect']) ? $config['general']['redirect'] : '';
    }

    /**
     * Initialise la config
     * @return Site
     */
    private function initConfig(){
		$this->config = Loader::getInstance()->getPathConfig($this->getPath().'/site');
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

    /**
     * Renvoi un module en fonction des paramètres
     * @param $pageUrl
     * @param $zoneName
     * @param $uniqId
     * @return Module
     * @throws \Exception
     */
    public function findModule($pageUrl, $zoneName, $uniqId){
		$page = $this->getPage($pageUrl);
		if(is_null($page)){
			throw new \Exception('Page '.$pageUrl.' introuvable');
		}
		foreach($page->getZone($zoneName)->getModules() as $module){
			if($module->getUniqId() == $uniqId){
				return $module;
			}
		}
		throw new \Exception('Impossible de trouver le module');
	}
	
}