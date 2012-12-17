<?php

namespace Qwik\Kernel\App\Page;

use Qwik\Kernel\App\Zone\Zone;
use Qwik\Kernel\App\Language;


class Page {

	private $config;
	private $site;
	private $url;
	private $isHidden;
    private $cachedFiles;
	
	public function __construct(){
		$this->config = array();
        $this->cachedFiles = array();
	}
	
	public function setConfig($config){
		$this->config = $config;
	}
	
	public function getConfig(){
		return $this->config;
	}
	
	public function setIsHidden($hidden){
		$this->isHidden = $hidden;
	}
	public function isHidden(){
		return $this->isHidden;
	}
	
	public function setSite($site){
		$this->site = $site;
	}
	
	public function getSite(){
		return $this->site;
	}
	
	public function getUrl(){
		return $this->url;
	}
	public function setUrl($url){
		$this->url = $url;
	}
	
	public function getTemplate(){
		$config = $this->getConfig();
		return $config['template'];
	}
	
	public function getTitle(){
		$config = $this->getConfig();
		return isset($config['title']) ? Language::getValue($config['title']) : '';
	}

	
	public function getZone($zoneName){
	
		//Get la config de la page
		$config = $this->getConfig();
		
		//Si on a pas de zone avec ce nom, on fait une zone avec une config vide
		return $this->getBuildedZone($zoneName, isset($config['zones'][$zoneName])? $config['zones'][$zoneName] : array());
	}

    /**
     * @param $type (css|javascript)
     * @return array
     */
    public function getFiles($type){
        $this->cachedFiles = array();
        $this->cachedFiles['javascript'] = array();
        $this->cachedFiles['css'] = array();
		$config = $this->getConfig();
		if(!empty($config['zones'])){
			foreach($config['zones'] as $zoneName => $zoneConfig){
				$filesOfZone = $this->getBuildedZone($zoneName, $zoneConfig)->getFiles();
                $this->cachedFiles['javascript'] = array_merge($this->cachedFiles['javascript'], $filesOfZone['javascript']);
                $this->cachedFiles['css'] = array_merge($this->cachedFiles['css'], $filesOfZone['css']);
			}
            $this->cachedFiles['javascript'] = array_unique($this->cachedFiles['javascript']);
            $this->cachedFiles['css'] = array_unique($this->cachedFiles['css']);
		}

		
		return isset($this->cachedFiles[$type]) ? $this->cachedFiles[$type] : array();
	}

	private function getBuildedZone($name, $config){
		$zone = new Zone();
		$zone->setPage($this);
		$zone->setConfig($config);
		$zone->setName($name);
		return $zone;
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