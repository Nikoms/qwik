<?php

namespace Qwik\Kernel\App\Site;


//use Symfony\Component\Yaml\Yaml;
use Qwik\Kernel\App\Page\Page;



use Qwik\Kernel\App\Language;

class Site {

	private $config;
	private $domain;
	private $path;
	private $www;
	
	public function __construct(){
	}
	
	
	public function getConfig(){
		if(is_null($this->config)){
			$this->initConfig();
		}
		return $this->config;
	}
	
	
	public function getDomain(){
		return $this->domain;
	}
	public function setDomain($domain){
		$this->domain = $domain;
	}
	public function getWww(){
		return $this->www;
	}
	public function setWww($www){
		$this->www = $www;
	}
    //Path utiliser pour rediriger vers getRealUploadPath (voir htaccess)
    public function getVirtualUploadPath(){
        return 'q/';
    }
    //Le dossier public pour le site
    public function getRealUploadPath(){
        return 'pissette/'. $this->getDomain();
    }
	
	public function getPath(){
		return $this->path;
	}
	public function setPath($path){
		$this->path = $path;
	}

    public function getConfigErrors(){
        $config = $this->getConfig();
        return isset($config['errors']) ? $config['errors'] : array();
    }
	
	public function getConfigPages(){
		$config = $this->getConfig();
		return $config['pages'];
	}
	
	public function getDefaultLanguage(){
		$languages = $this->getLanguages();
		return (count($languages) > 0) ? $languages[0] : false;
	}
	
	public function getLanguages(){
		$config = $this->getConfig();
		return isset($config['general']['languages']['available']) ? $config['general']['languages']['available'] : array();
	}
    public function getTitle(){
        $config = $this->getConfig();
        return isset($config['general']['title']) ? Language::getValue($config['general']['title']) : '';
    }


	public function getFirstPage(){
		return $this->getPage(key($this->getConfigPages()));
	}

	public function getPage($url){
		$url = (string) $url;
		
		foreach($this->getConfigPages() as $name => $config){
			if($name === $url){
				return $this->getBuildedPage($name, $config);
			}
		}
		return null;
	}
    public function getError(\Exception $exception, $uri){
        $code = $exception->getCode();
        $errors = $this->getConfigErrors();
        //Si on trouve pas le code, on prend default
        if(!isset($errors[$code])){
            $code = 'default';
        }
        if(isset($errors[$code])){
            return $this->getBuildedPage('error_' . $code, $errors[$code]);
        }
        return null;
    }
	
	public function getPages(){
		$pages = array();
		foreach($this->getConfigPages() as $name => $config){
			$pages[] = $this->getBuildedPage($name, $config);
		}
		return $pages;
	}

	private function getBuildedPage($name, $config){
		$page = new Page();
		$page->setConfig($config);
		$page->setSite($this);
		$page->setUrl($name);
		$page->setIsHidden(!empty($config['hidden']));
		return $page;
	}
	
	public function exists(){
		$config = $this->getConfig();
		return isset($config['general']);
	}

    public function isAlias(){
        return $this->getRedirect() !== '';
    }
    public function getRedirect(){
        $config = $this->getConfig();
        return isset($config['general']['redirect']) ? $config['general']['redirect'] : '';
    }
	
	private function initConfig(){
		$this->config = \Qwik\Kernel\App\Config::getInstance()->getConfig($this->getPath().'/config');
	}
	
	
	public function getGoogleAnalytics(){
		$config = $this->getConfig();
		return (isset($config['general']['google']) && isset($config['general']['google']['analytics'])) ? (string) $config['general']['google']['analytics'] : '';
	}
	
}