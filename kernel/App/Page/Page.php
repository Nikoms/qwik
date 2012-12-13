<?php

namespace Qwik\Kernel\App\Page;

use Qwik\Kernel\App\Zone\Zone;
use Qwik\Kernel\App\Language;


class Page {

	private $config;
	private $site;
	private $url;
	private $isHidden;
	
	public function __construct(){
		$this->config = array();
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
		return Language::getValue($config['title']);
	}
	
	public function getZone($zoneName){
	
		//Get la config de la page
		$config = $this->getConfig();
		
		//Si on a pas de zone avec ce nom, on fait une zone avec une config vide
		return $this->getBuildedZone($zoneName, isset($config['zones'][$zoneName])? $config['zones'][$zoneName] : array());
	}
	
	public function getFiles(){
		$files = array();
		$files['javascript'] = array();
		$files['css'] = array();
		$config = $this->getConfig();
		if(!empty($config['zones'])){
			foreach($config['zones'] as $zoneName => $zoneConfig){
				$filesOfZone = $this->getBuildedZone($zoneName, $zoneConfig)->getFiles();
				$files['javascript'] = array_merge($files['javascript'], $filesOfZone['javascript']);
				$files['css'] = array_merge($files['css'], $filesOfZone['css']);
			}
			$files['javascript'] = array_unique($files['javascript']);
			$files['css'] = array_unique($files['css']);
		}		
		
		return $files;
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