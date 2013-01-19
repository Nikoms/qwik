<?php

namespace Qwik\Kernel\App\Page;

use Qwik\Kernel\App\Zone\Zone;
use Qwik\Kernel\App\Language;
use Qwik\Kernel\App\Site\Site;


/**
 * Classe représentant une page d'un site
 */
class Page {

    /**
     * Configuration de la page
     * @var array
     */
    private $config;
    /**
     * Site de la page
     * @var Site
     */
    private $site;
    /**
     * Url de la page
     * @var string
     */
    private $url;
    /**
     * Indique si la page est cachée ou non. Attention, cachée ne veut pas dire désactivée. La page sera encore accessible.
     * @var bool
     */
    private $isHidden;
    /**
     * fichiers statiques (css, javascript) nécessaires au bon fonctionnement de la page (récupération dans les modules)
     * @var array
     */
    private $staticFiles;


    /**
     * Tableau des zones de la page
     * @var Zone[]
     */
    private $zones;
	
	public function __construct(){
		$this->config = array();
        $this->staticFiles = array();
	}

    /**
     * @param array $config
     */
    public function setConfig(array $config){
		$this->config = $config;
	}

    /**
     * @return array
     */
    public function getConfig(){
		return $this->config;
	}
	/**
     * @param $isHidden bool
     */
    public function setIsHidden($isHidden){
		$this->isHidden = (bool) $isHidden;
	}

    /**
     * @return bool
     */
    public function isHidden(){
		return $this->isHidden;
	}

    /**
     * @param \Qwik\Kernel\App\Site\Site $site
     */
    public function setSite(Site $site){
		$this->site = $site;
	}

    /**
     * @return \Qwik\Kernel\App\Site\Site
     */
    public function getSite(){
		return $this->site;
	}

    /**
     * @return string
     */
    public function getUrl(){
		return $this->url;
	}

    /**
     * @param $url string
     */
    public function setUrl($url){
		$this->url = trim((string) $url);
	}

    /**
     * Retourne le nom du template de la page.
     * @return mixed
     * @throws \Exception Si pas de template
     */
    public function getTemplate(){
        $config = $this->getConfig();
        if(empty($config['template'])){
            throw new \Exception('Template non défini');
        }
		return $config['template'];
	}

    /**
     * Renvoi le titre de la page
     * @return string
     */
    public function getTitle(){
		$config = $this->getConfig();
		return isset($config['title']) ? Language::getValue($config['title']) : '';
	}

    /**
     * Retourne la zone en fonction de son nom
     * Cette méthode, en plus de récupérer la zone, la rajoute dans le tableau des zones de la page. Pourquoi? Car on appelle jamais getZones, sauf à la fin, et pendant le chargement de la page, on appelle individuellement chaque zone. Pas besoin de les recalculer donc :)
     * @param string $zoneName
     * @return \Qwik\Kernel\App\Zone\Zone
     * @throws \Exception Si la zone n'a pas été trouvée
     */
    public function getZone($zoneName){

        //Récup des zones
        $zones = $this->getZones();

        //Pas de zone trouvée
        if(!isset($zones[$zoneName])){
            throw new \Exception('Impossible de trouver la zone "'.$zoneName.'"');
        }

        return $zones[$zoneName];
	}


    /**
     * @return \Qwik\Kernel\App\Zone\Zone[]
     */
    public function getZones(){

        if(is_null($this->zones)){
            $zoneManager = new \Qwik\Kernel\App\Zone\ZoneManager();
            $this->zones = $zoneManager->getByPage($this);
        }

        return $this->zones;
    }

    /**
     * @param $type (css|javascript)
     * @return array
     */
    public function getFiles($type){

        if(empty($this->staticFiles)){
            $this->staticFiles = array();
            $this->staticFiles['javascript'] = array();
            $this->staticFiles['css'] = array();

            foreach($this->getZones() as $zone){
                $filesOfZone = $zone->getFiles();
                $this->staticFiles['javascript'] = array_merge($this->staticFiles['javascript'], $filesOfZone['javascript']);
                $this->staticFiles['css'] = array_merge($this->staticFiles['css'], $filesOfZone['css']);
            }
            $this->staticFiles['javascript'] = array_unique($this->staticFiles['javascript']);
            $this->staticFiles['css'] = array_unique($this->staticFiles['css']);
        }
		
		return isset($this->staticFiles[$type]) ? $this->staticFiles[$type] : array();
	}

    public function getKeywords(){
        $config = $this->getConfig();
        if(empty($config['meta']) || empty($config['meta']['keywords'])){
            return '';
        }
        return $config['meta']['keywords'];
    }

    public function getDescription(){
        $config = $this->getConfig();
        if(empty($config['meta']) || empty($config['meta']['description'])){
            return '';
        }
        return $config['meta']['description'];
    }
	
	
	

}