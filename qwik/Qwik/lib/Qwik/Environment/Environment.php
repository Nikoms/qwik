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

    public function __construct(Application $app, $env){
        $this->setApp($app);
        $this->setEnv($env);
        parent::__construct($this->loadConfig());
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
        $value = parent::get($path, $defaultValue);
         return $this->replaceVars($value);
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
            $app = $this->getApp()->getSilex();
            return str_replace(
                array('%site_path%', '%kernel_path%'),
                array($app['site']->getPath(), __DIR__ . '/..'),
                $var
            );
        }
    }

    public function __toString(){
        return $this->getEnv();
    }
}

