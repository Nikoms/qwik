<?php

namespace Qwik\Component\Routing;

/**
 * Une route, qui correspond à une url et une méthode callback
 */
class Route {

    /**
     * @var callable méthode à appeler pour récupérer le contenu de la page
     */
    private $callable;
    /**
     * @var array Tableau des valeurs récupérés via l'url
     */
    private $assert; //regex
    /**
     * @var array méthode acceptées lors de l'appel à la page (GET/POST)
     */
    private $methods;
    /**
     * @var string Pattern de la page à interprété
     */
    private $path;
    /**
     * @var string Nom de la route
     */
    private $name;

    /**
     *
     */
    public function __construct(){
        $this->methods = array();
        $this->assert = array();
    }

    /**
     * @param $callable callable
     * @return Route
     */
    public function setCallable($callable){
        if(!is_callable($callable)){
            throw new \Exception('Callable is not callable :)');
        }
        $this->callable = $callable;
        return $this;
    }

    /**
     * @return callable
     */
    public function getCallable(){
        return $this->callable;
    }

    /**
     * Ajoute un pattern pour une variable censée être récupérée dans l'adresse
     * @param $var string
     * @param $assert string
     * @return Route
     */
    public function assert($var, $assert){
        $this->assert[(string) $var] = (string) $assert;
        return $this;
    }

    /**
     * Récupère un pattern suivant le nom de la variable à récupéré dans l'adresse
     * @param $var string
     * @return string
     */
    public function getAssert($var){
        if(!isset($this->assert[$var])){
            //Par défaut c'est du text/chiffre, underscore, point tiret
            return '[a-zA-Z0-9_\.-]+';
        }
        return $this->assert[$var];
    }

    /**
     * Ajoute une méthode possible pour l'accès à la page
     * @param $method string GET|POST
     * @return Route
     */
    public function addMethod($method){
        $this->methods[] = trim((string) $method) == 'POST'? 'POST' : 'GET';
        return $this;
    }

    /**
     * @return array Tableau de méthode
     */
    public function getMethods(){
        return $this->methods();
    }

    /**
     * @param $path string
     * @return Route
     */
    public function setPath($path){
        $this->path = (string) $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(){
        return $this->path;
    }

    /**
     * @param $name string
     * @return Route
     */
    public function setName($name){
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }


    /**
     * Renvoi la liste des valeurs récupérées dans l'adresse (grace au pattern du path)
     * @param $path string Path dont il faut retiré les valeurs
     * @return array tableau indexé
     */
    public function getVarForPath($path){
        preg_match('#^'.$this->getRegex().'$#', (string) $path, $matches);
        array_shift($matches);
        return $matches;
    }

    /**
     * Indique sur le path donné en paramètre correspond à l'url du pattern de la route
     * @param $path string
     * @return bool
     */
    public function matchWith($path){
        /*echo $this->getPath().' ===> ';
        echo '#^'.$this->getRegex().'$#';
        echo ' VS ';
        echo $path;
        $found = preg_match('#^'.$this->getRegex().'$#', $path, $matches);
        echo '<br />';
        var_dump($found);
        var_dump($matches);
        echo '<hr >';*/

        return (bool) preg_match('#^'.$this->getRegex().'$#', (string) $path, $matches);
    }


    /**
     * Renvoi la string qui devra être utilisée pour savoir si l'url du visiteur correspont au pattern du "path" de la route
     * @return string
     */
    private function getRegex(){
        //On met this dans route, pour le use de la fonction anonyme
        $route = $this;
        //Remplacement de tout ce qui se trouve entre "{}"  (Caractères acceptés : alphanumériques + underscore)
        return preg_replace_callback('#{[a-zA-Z0-9_]+}#', function($matches) use ($route){
            $var = substr($matches[0], 1, -1);
            return '('.$route->getAssert($var).')';
        }, $this->getPath());
    }


}