<?php

namespace Qwik\Kernel\App\Zone;

use Qwik\Kernel\App\Module\Module;
use Qwik\Kernel\App\Page\Page;

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
     * @param \Qwik\Kernel\App\Page\Page $page
     */
    public function setPage(Page $page){
		$this->page = $page;
	}

    /**
     * @return \Qwik\Kernel\App\Page\Page
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
     * @return array|\Qwik\Kernel\App\Module\Module[] Tableau des modules
     */
    public function getModules(){
		if(is_null($this->modules)){
            $moduleManager = new \Qwik\Kernel\App\Module\ModuleManager();
            $this->modules = $moduleManager->getByZone($this);
		}
		return $this->modules;
	}

    /**
     * @return string Concaténation de tous les modules
     */
    public function __toString(){
		return implode('', $this->getModules());
	}

    /**
     * Renvoi les fichiers statiques (js,css) nécessaires pour le bon affichage de la page.
     * On demande simplement aux modules de la zone de bien vouloir donner leur fichiers et on fait le récap :)
     * @return array
     */
    public function getFiles(){
		$files = array();
		$files['javascript'] = array();
		$files['css'] = array();

        //Modules, donnéez moi vos fichiers statiques
		foreach ($this->getModules() as $module){
			$files['javascript'] = array_merge($files['javascript'], $module->getConfigObject()->getFiles('javascript'));
			$files['css'] = array_merge($files['css'], $module->getConfigObject()->getFiles('css'));
		}

        //On fait un array_unique, car si plusieurs modules utilisent le meme js/css, on ne le prend qu'une fois
		$files['javascript'] = array_unique($files['javascript']);
		$files['css'] = array_unique($files['css']);
		return $files;
	}

}