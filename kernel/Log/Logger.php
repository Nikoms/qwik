<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 21/12/12
 * Time: 3:26
 * To change this template use File | Settings | File Templates.
 */
namespace Qwik\Kernel\Log;

class Logger{

    static private $instance;

    private $isStarted;
    private $logs;


    private function __construct(){
        $this->logs = array();
        $this->start();
        register_shutdown_function(array($this,'shutdown'));
        set_error_handler(array($this,'errorHandler'));
    }

    static public function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    private function start(){
        //echo 'start';
    }
    private function stop(){
       // echo 'stop';
    }
    public function errorHandler($number , $message, $file, $line, $context){
        //Prend le md5 de l'erreur = unique pour un message
        $id = md5($message . '+' . $file . '+' . $line);
        //Si on a déjà le message, ca ne nous interesse plus, à part pour rajouter le count :)
        if(isset($this->logs[$id])){
            $this->logs[$id]['count'] ++ ;
            return;
        }
        //On regroupe toutes les erreurs. Pas besoin de toutes les avoir plusieurs fois
        $this->logs[$id] =  array(
            'message' => 'error ' . $number. ' - ' . $message.' / '.$file.':'.$line,
            'count' => 1
        );
    }
    public function shutdown(){
        $this->stop();
        $this->showLogs();
    }
    private function showLogs(){
        if(!empty($this->logs)){
            print_r($this->logs);
        }

    }

}