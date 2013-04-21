<?php

namespace Qwik\Cms\Module;

use Qwik\Cms\AppManager;
use Qwik\Cms\Site\Site;
use Qwik\Component\Config\Loader;
use Qwik\Component\Locale\Language;
use Qwik\Component\Template\TemplateProxy;
use Qwik\Cms\Zone\Zone;

/**
 * Class Module
 * @package Qwik\Cms\Module
 */
abstract class Module {

    /**
     * Zone dans laquelle appartient le module
     * @var \Qwik\Cms\Zone\Zone
     */
    private $zone;
    /**
     * La config du module indiquée dans le yml de la page
     * @var Config
     */
    private $config;

    /**
     * Config générale du module
     * @var Config
     */
    private $moduleConfig;
    /**
     * Id unique du module
     * @var string
     */
    private $uniqId;


    /**
     *
     */
    public function __construct(){
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config){
		$this->config = $config;
	}

    /**
     * @return Config
     */
    public function getConfig(){
        return $this->config;
	}

    public function getModuleConfig(){
        //Si pas déjà instancié
        if($this->moduleConfig === null){
            $classInfo = new \ReflectionClass($this);
            //On va calculer le path de la config du module
            $pathOfConfig = dirname($classInfo->getFileName()) . DIRECTORY_SEPARATOR . 'config';
            //Récupération de la config sous forme d'array
            $config = Loader::getInstance()->getPathConfig($pathOfConfig);
            //Transformation de l'array en objet
            $this->moduleConfig = new Config($config);
        }
        return $this->moduleConfig;
    }

    /**
     * @param Zone $zone
     */
    public function setZone(Zone $zone){
		$this->zone = $zone;
	}

    /**
     * @return Zone
     */
    public function getZone(){
		return $this->zone;
	}

    /**
     * Nom de la classe en cours, sans namespace
     * @return string
     */
    public function getName(){
        $class = explode('\\', get_class($this));
        return end($class);
	}

    /**
     * @param $uniqId string
     */
    public function setUniqId($uniqId){
		$this->uniqId = trim((string) $uniqId);
	}

    /**
     * @return string
     */
    public function getUniqId(){
		return $this->uniqId;
	}

    /**
     * Récupération d'une traduction du fichier "translations.yml" dans la config du module en cours
     * @param $key
     * @return mixed
     */
    public function translate($key){
        return $this->getModuleConfig()->get('translations.' . $key);
    }

    /**
     * Render du module dans la page
     * @return string
     */
    public function __toString(){
		try{
			return TemplateProxy::getInstance()->renderModule($this);
		}catch(\Exception $ex){
			return $ex->getMessage();
		}
	}

    /**
     * Récupération du template
     * @return string
     */
    public function getTemplatePath(){
		return $this->getName() . '/views/display.html.twig';
	}

    /**
     * Récupération des variables utile pour le template. Par défaut c'est ce qui se trouve dans la config, mais ceci est destiné à souvent être réécrit lors de l'héritage
     * @return array
     */
    public function getTemplateVars(){
		return $this->getConfig()->toArray();
	}



}