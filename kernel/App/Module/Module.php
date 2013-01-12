<?php

namespace Qwik\Kernel\App\Module;

use Qwik\Kernel\App\Language;
use Qwik\Kernel\App\Zone\Zone;


abstract class Module {


    /**
     * Nom du module
     * @var string
     */
    //private $name;
    /**
     * Zone dans laquelle appartient le module
     * @var \Qwik\Kernel\App\Zone\Zone
     */
    private $zone;
    /**
     * La config du module
     * @var array
     */
    private $config;
    /**
     * La config sous forme d'objet "Config"
     * @var Config
     */
    private $configObject;
    /**
     * Id unique du module
     * @var string
     */
    private $uniqId;

    /**
     * Ajout de route, si nécessaire, pour les modules
     * @param \Qwik\Kernel\App\AppManager $app
     * @param \Qwik\Kernel\App\Site\Site $site
     */
    public static function injectInApp(\Qwik\Kernel\App\AppManager $appManager, \Qwik\Kernel\App\Site\Site $site){

	}

    /**
     * Récupération d'un module en fonction de sa config
     * @param $config array|string path vers la config ou array de config
     * @param Zone $zone
     * @param $uniqId string
     * @return Module
     */
    public static function get($config, Zone $zone, $uniqId){
        $uniqId = (string) $uniqId;

        if(is_array($config)){
            return self::getWithArray($config, $zone, $uniqId);
        }
        return self::getWithFile((string) $config, $zone, $uniqId);
    }

    /**
     * Renvoi le nom de la classe (avec namespace) du module $name
     * @param $name string
     * @return string
     */
    public static function getClassName($name){
        //Le nom c'est une majuscule et le reste en minuscule
        $name = ucfirst(strtolower(trim((string) $name)));
		return '\Qwik\Kernel\Module\\' . $name . '\Entity\\' . $name;
	}

    /**
     * Renvoi le path où se trouve les modules
     * @return string
     */
    public static function getModulesPath(){
        //TODO: A mettre quelque part en config
        return __DIR__ . '/../../Module';
    }


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
     * @param \Qwik\Kernel\App\Zone\Zone $zone
     */
    public function setZone(Zone $zone){
		$this->zone = $zone;
	}

    /**
     * @return \Qwik\Kernel\App\Zone\Zone
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
     * Retourne la config du module en objet Config
     * @return Config
     */
    public function getConfigObject(){
        //Si pas déjà instancié
		if(is_null($this->configObject)){
            //On va calculer le path de la config du module
            $pathOfConfig = self::getModulesPath() . DIRECTORY_SEPARATOR . $this->getName() . DIRECTORY_SEPARATOR . 'config';
            //Récupération de la config sous forme d'array
			$config = \Qwik\Kernel\App\Config::getInstance()->getConfig($pathOfConfig);
            //Transformation de l'array en objet
			$this->configObject = new Config($config);
		}
		return $this->configObject;
	}

    /**
     * Récupération d'une traduction du fichier "translations.yml" dans la config du module en cours
     * @param $key
     * @return mixed
     */
    public function translate($key){
        return $this->getConfigObject()->get('translations.' . $key);
    }

    /**
     * Render du module dans la page
     * @return string
     */
    public function __toString(){
		try{
			return \Qwik\Kernel\App\TemplateProxy::getInstance()->renderModule($this);
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
		return $this->getConfig();
	}



    /**
     * Construction d'un module en fonction d'un array config
     * @param array $config
     * @param Zone $zone
     * @param $uniqId
     * @return Module
     * @throws \Exception
     */
    static private function getWithArray(array $config, Zone $zone, $uniqId){
        $uniqId = (string) $uniqId;

        if(empty($config['module'])){
            throw new \Exception("Module empty!");
        }

        //Récupération du nom de la classe (avec namespace)
        $className = self::getClassName($config['module']);

        if(!class_exists($className)){
            throw new \Exception('Module '.$config['module'].' not found');
        }

        $module = new $className();
        $module->setConfig($config['config']);
        $module->setZone($zone);
        $module->setUniqId($uniqId);
        return $module;
    }

    /**
     * Construction d'un module en fonction d'une string (path vers fichier dans "resources")
     * @param $filePath
     * @param \Qwik\Kernel\App\Zone\Zone $zone
     * @param $uniqId
     * @return Module
     * @throws \Exception
     */
    static private function getWithFile($filePath, Zone $zone, $uniqId){
        $filePath = (string) $filePath;
        $uniqId = (string) $uniqId;

        $file = $zone->getPage()->getSite()->getPath() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filePath);
        if(!file_exists($file)){
            throw new \Exception('File ' . $file . ' not found (for module creation)');
        }
        return self::getWithArray(\Symfony\Component\Yaml\Yaml::parse($file), $zone, $uniqId);
    }




}