<?php

namespace Qwik\Cms\Zone;

use Qwik\Cms\Module\Info;
use Qwik\Cms\Module\Module;
use Qwik\Cms\Module\ModuleManager;
use Qwik\Cms\Page\Page;

/**
 * Une zone dans une page qui contient des modules
 */
class Zone {

    /**
     * @var array Tableau de la config de la zone
     */
    private $config;
    /**
     * @var Page Page à laquelle appartient la zone
     */
    private $page;
    /**
     * @var Module[] Tableau de module se retrouvant dans la zone
     */
    private $modules;

    /**
     * @var string Nom de la zone
     */
    private $name;

    /**
     *
     */
    public function __construct(){
		$this->config = array();
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
     * @param \Qwik\Cms\Page\Page $page
     */
    public function setPage(Page $page){
		$this->page = $page;
	}

    /**
     * @return \Qwik\Cms\Page\Page
     */
    public function getPage(){
		return $this->page;
	}

    /**
     * @param $name string
     */
    public function setName($name){
		$this->name = (string) $name;
	}

    /**
     * @return string
     */
    public function getName(){
		return $this->name;
	}

    /**
     * @return Info[] Tableau des modules
     */
    public function getModules(){
		if(is_null($this->modules)){
            $moduleManager = new ModuleManager();
            $this->modules = $moduleManager->getByZone($this);
		}
		return $this->modules;
	}


    /**
     * Renvoi les fichiers statiques (js,css) nécessaires pour le bon affichage de la page.
     * On demande simplement aux modules de la zone de bien vouloir donner leur fichiers et on fait le récap :)
     * @return array
     */
    public function getAssetsByType(){
		$files = array();
		$files['javascript'] = array();
		$files['css'] = array();

        //Modules, donnéez moi vos fichiers statiques
		foreach ($this->getModules() as $moduleInfo){
			$files['javascript'] = array_merge($files['javascript'], $moduleInfo->getModuleConfig()->getAssets('javascript'));
			$files['css'] = array_merge($files['css'], $moduleInfo->getModuleConfig()->getAssets('css'));
		}

        //On fait un array_unique, car si plusieurs modules utilisent le meme js/css, on ne le prend qu'une fois
		$files['javascript'] = array_unique($files['javascript']);
		$files['css'] = array_unique($files['css']);
		return $files;
	}

}