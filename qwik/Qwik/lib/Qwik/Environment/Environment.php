<?php

namespace Qwik\Environment;

use Qwik\Cms\AppManager;
use Qwik\Component\Config\Config;

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
        parent::__construct($this->load());
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
    private function load(){
        $myConfigFile = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $this->getEnv().'.yml';
        $allConfigFile = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'all.yml';
        if(!file_exists($myConfigFile)){
            throw new \Exception('Config file '.$this->getEnv().' not found');
        }

        $config = new \Qwik\Component\Config\Loader();
        $myConfig = $config->getFileConfig($myConfigFile);
        $allConfig = $config->getFileConfig($allConfigFile);

        return $this->array_merge_replace_recursive($allConfig, $myConfig);
    }



    /**
     * Merges any number of arrays of any dimensions, the later overwriting
     * previous keys, unless the key is numeric, in whitch case, duplicated
     * values will not be added.
     *
     * The arrays to be merged are passed as arguments to the function.
     *
     * @access public
     * @return array Resulting array, once all have been merged
     * @author Php.net : drvali at hotmail dot com
     */
    private function array_merge_replace_recursive() {
        // Holds all the arrays passed
        $params = func_get_args ();

        // First array is used as the base, everything else overwrites on it
        $return = array_shift ( $params );

        // Merge all arrays on the first array
        foreach ( $params as $array ) {
            foreach ( $array as $key => $value ) {
                // Numeric keyed values are added (unless already there)
                if (is_numeric ( $key ) && (! in_array ( $value, $return ))) {
                    if (is_array ( $value )) {
                        $return [] = $this->array_merge_replace_recursive ( $return [$key], $value );
                    } else {
                        $return [] = $value;
                    }

                    // String keyed values are replaced
                } else {
                    if (isset ( $return [$key] ) && is_array ( $value ) && is_array ( $return [$key] )) {
                        $return [$key] = $this->array_merge_replace_recursive ( $return [$key], $value );
                    } else {
                        $return [$key] = $value;
                    }
                }
            }
        }

        return $return;
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

