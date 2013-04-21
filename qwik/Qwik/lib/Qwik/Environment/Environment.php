<?php

namespace Qwik\Environment;

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

    public function __construct($env, AppManager $app){
        $this->setApp($app);
        $this->setEnv($env);
        parent::__construct($this->loadConfig());
    }

    /**
     * @param \Qwik\Cms\AppManager $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

    /**
     * @return \Qwik\Cms\AppManager
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
     * Charge la configuration
     */
    private function loadConfig(){
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . 'Resources\config' . DIRECTORY_SEPARATOR;
        $myConfigFile = $configPath . $this->getEnv().'.yml';
        $allConfigFile = $configPath . 'all.yml';

        $config = new Loader();
        return $config->merge($config->getFileConfig($allConfigFile), $config->getFileConfig($myConfigFile));
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
            return str_replace(
                array('%site_path%', '%kernel_path%'),
                array($this->getApp()->getSite()->getPath(), __DIR__ . '/..'),
                $var
            );
        }
    }
}

