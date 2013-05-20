<?php

namespace Qwik\Environment;

use Qwik\Application;
use Qwik\Cms\AppManager;
use Qwik\Component\Config\Config;
use Qwik\Component\Config\Loader;

class Environment extends Config{
    /**
     * @var string Environnement
     */
    private $env;

    /**
     * @var AppManager
     */
    private $app;

    private $convert;

    public function __construct(Application $app, $env){
        $this->convert = array();
        $this->setApp($app);
        $this->setEnv($env);
        parent::__construct($this->loadConfig());
    }

    public function addConvert($key, $value){
        $this->convert[$key] = $value;
    }

    /**
     * @param \Qwik\Application $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

    /**
     * @return \Qwik\Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param string $env
     */
    public function setEnv($env){
        $this->env = (string) $env;

    }

    /**
     * @return string
     */
    public function getEnv(){
        return $this->env;
    }


    /**
     * @param string $path
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($path, $defaultValue = null){
        return $this->replaceVars(parent::get($path, $defaultValue));
    }

    /**
     * @return string
     */
    private function getConfigPath(){
        return  __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . 'Resources\config' . DIRECTORY_SEPARATOR;
    }

    /**
     * Charge la configuration
     */
    private function loadConfig(){
        $loader = new Loader();

        $allConfig = $loader->getFileConfig($this->getConfigPath() . 'config.yml');
        $myConfigFile = $this->getConfigPath() . 'config_'.$this->getEnv().'.yml';
        if(!file_exists($myConfigFile)){
            return $allConfig;
        }

        return $loader->merge($allConfig, $loader->getFileConfig($myConfigFile));
    }



    private function replaceVars($var){
        if(is_bool($var) || is_numeric($var)){
            return $var;
        }elseif(is_array($var)){
            $return = array();
            foreach($var as $key => $arrayVar){
                $return[$key] = $this->replaceVars($arrayVar);
            }
            return $return;
        }else{
            $return = $var;
            foreach($this->convert as $key => $value){
                $return = str_replace('%'.$key.'%', $value, $return);
            }
            return $return;
        }
    }

    public function __toString(){
        return $this->getEnv();
    }
}

