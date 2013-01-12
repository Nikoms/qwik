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

    /**
     * constante pour la fatal error. 3 n'est pas encore pris
     */
    const FATAL_ERROR = 3;
    /**
     * @var Logger Instance du Logger
     */
    static private $instance;

    /**
     * @var bool Indique si on a commencer le log
     */
    //private $isStarted;
    /**
     * @var array Tableau des logs regroupé par "md5"
     */
    private $logs;


    /**
     * @var array Backtrace de la dernière erreur
     */
    private $lastErrorBacktrace = array();

    /**
     *
     */
    private function __construct(){
        $this->logs = array();
        //$this->start();
        //Quand c'est fini, on passe par le shutdown du Logger
        register_shutdown_function(array($this,'shutdown'));
        //La gestion des erreurs se fait via notre errorHandler
        set_error_handler(array($this,'errorHandler'));
    }

    /**
     * @return Logger
     */
    static public function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    /**
     * Démarrage du log
     */
    private function start(){
        //echo 'start';
    }

    /**
     * Arret du log (= de la page)
     */
    private function stop(){
       // echo 'stop';
    }

    /**
     * @param string $message Message à logger venant du développeur
     * @param null $var variable qui peut être utilisé pour être loggée (pas encore activé)
     */
    public function log($message, $var = null){
        $this->initLastUserErrorBacktrace();
        trigger_error ((string) $message, E_USER_NOTICE);
    }

    /**
     * @param string $message Warning à logger venant du développeur
     * @param null $var variable qui peut être utilisé pour être loggée (pas encore activé)
     */
    public function warning($message, $var = null){
        $this->initLastUserErrorBacktrace();
        trigger_error ($message, E_USER_WARNING);
    }

    /**
     * @param string $message Erreur à logger venant du développeur
     * @param null $var variable qui peut être utilisé pour être loggée (pas encore activé)
     */
    public function error($message, $var = null){
        $this->initLastUserErrorBacktrace();
        trigger_error ($message, E_USER_ERROR);
    }
    /**
     * @param string $message Message déprécié à logger venant du développeur
     * @param null $var variable qui peut être utilisé pour être loggée (pas encore activé)
     */
    public function deprecated($message, $var = null){
        $this->initLastUserErrorBacktrace();
        trigger_error ($message, E_USER_DEPRECATED);
    }

    /**
     * Handler des erreurs
     * @param int $number Numéro de l'erreur
     * @param string $message Message
     * @param string $file Fichier qui a appelé le handler
     * @param string $line Ligne du fichier qui a appelé le handler
     */
    public function errorHandler($number , $message, $file, $line){
        //Si on a pas de backtrace, alors il est temps de la prendre (on en a une  quand on est passé par log, warning, error, deprecated)
        if(count($this->getLastErrorBacktrace()) === 0){
            $this->setLastErrorBacktrace($this->getDebugBacktraceLight());
        }

        //Prend le md5 de l'erreur = unique pour un message
        $id = md5($message . '+' . $file . '+' . $line);

        //Si on a déjà le message, ca ne nous interesse plus, à part pour rajouter le count :)
        if(isset($this->logs[$id])){
            $this->logs[$id]['count'] ++ ;
            return;
        }

        //On regroupe toutes les erreurs. Pas besoin de toutes les avoir plusieurs fois
        $this->logs[$id] =  array(
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'errorNumber' => $number,
            'errorName' => $this->getErrorName($number),
            'count' => 1,
            'backtrace' => $this->getLastErrorBacktrace(),
        );
        $this->emptyLastErrorBacktrace();

    }

    /**
     * @return array Le backtrace de la dernière erreur
     */
    private function getLastErrorBacktrace(){
        return $this->lastErrorBacktrace;
    }

    /**
     * @param array $lastErrorBacktrace
     */
    private function setLastErrorBacktrace(array $lastErrorBacktrace){
        $this->lastErrorBacktrace = $lastErrorBacktrace;
    }
    /**
     * Initialisation du backtrace quand on fait une erreur "E_USER_..."
     */
    private function initLastUserErrorBacktrace(){
        $this->lastErrorBacktrace = $this->getDebugBacktraceLight();
        //2 Array shit pour que l'array ai exactement l'endroit où a terminé l'erreur et donc enlever la logique interne
        array_shift($this->lastErrorBacktrace);
    }
    /**
     * Vide le backtrace de la dernière erreur
     */
    private function emptyLastErrorBacktrace(){
        $this->lastErrorBacktrace = array();
    }

    /**
     * Fin du script
     */
    public function shutdown(){
        //Check de la dernière erreur, au cas où c'est une fatal
        $this->checkLastError();
        //On arrête tout
        $this->stop();
        //On affiche les logs
        $this->showLogs();
    }

    /**
     * Check de la dernière erreur, en effet, si on a une fatal error, elle n'est pas catchée par l'error handler. Du coup quand on a fini la page on regarde si la dernière n'était pas une fatale, et on la log :)
     */
    private function checkLastError(){
        $lastError = error_get_last();
        if($lastError['type'] === E_ERROR || $lastError['type'] === E_USER_ERROR) {
            //On ne peut pas passé par "trigger_error", car cette méthode ne prend que les erreurs E_USER
            $this->errorHandler(Logger::FATAL_ERROR, $lastError['message'] . ' ('.$this->getErrorName($lastError['type']).')', $lastError['file'], $lastError['line']);
        }
    }

    /**
     * Affichage des logs
     */
    private function showLogs(){
        //Todo: afficher que si mode debug
        if(!empty($this->logs)){
            print_r($this->logs);
        }

    }

    /**
     * Renvoi l'équivalent du numéro de l'erreur mais sous forme de string (plus compréhensible donc)
     * @param int $errorNumber numéro de l'erreur (constante PHP)
     * @return string
     */
    private function getErrorName($errorNumber){
        switch($errorNumber){
            case E_ERROR:               return 'Error';
            case E_WARNING:             return 'Warning';
            case E_PARSE:               return 'Parse Error';
            case E_NOTICE:              return 'Notice';
            case E_CORE_ERROR:          return 'Core Error';
            case E_CORE_WARNING:        return 'Core Warning';
            case E_COMPILE_ERROR:       return 'Compile Error';
            case E_COMPILE_WARNING:     return 'Compile Warning';
            case E_USER_ERROR:          return 'User Error';
            case E_USER_WARNING:        return 'User Warning';
            case E_USER_NOTICE:         return 'User Notice';
            case E_STRICT:              return 'Strict Notice';
            case E_RECOVERABLE_ERROR:   return 'Recoverable Error';
            case Logger::FATAL_ERROR:    return 'Fatal Error';
            default:                    return 'Unknown error (' . $errorNumber . ')';
        }
    }


    /**
     * @return array
     */
    private function getDebugBacktraceLight(){
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //Enlève le premier, car le premier c'est l'appel à getDebugBacktraceLight
        array_shift($backtrace);
        return $backtrace;
    }

}