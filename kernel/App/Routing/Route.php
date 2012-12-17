<?php

namespace Qwik\Kernel\App\Routing;

class Route {

	private $callable;
	private $assert; //regex
	private $methods;
	private $path;
	private $name;
	
	public function __construct(){
		$this->methods = array();
		$this->assert = array();
	}

	public function setCallable($callable){
		$this->callable = $callable;
		return $this;
	}
	public function getCallable(){
		return $this->callable;
	}
	
	public function assert($var, $assert){
		$this->assert[$var] = $assert;
		return $this;
	}
	public function getAssert($var){
		if(!isset($this->assert[$var])){
			//Par défaut c'est du text/chiffre et underscore
			return '[a-zA-Z0-9_\.-]*';
		}
		return $this->assert[$var];
	}
	
	
	public function addMethod($method){
		$this->methods[] = $method;
		return $this;
	}
	
	public function getMethods(){
		return $this->methods();
	}
	
	public function setPath($path){
		$this->path = $path;
		return $this;
	}
	public function getPath(){
		return $this->path;
	}

	public function setName($name){
		$this->name = $name;
		return $this;
	}
	
	private function getRegex(){
		$route = $this;
		return preg_replace_callback('#{[a-zA-Z0-9_]+}#', function($matches) use ($route){
            $var = substr($matches[0], 1, -1);
			return '('.$route->getAssert($var).')';
		}, $this->getPath());
	}

    public function getVarForPath($path){
        preg_match('#^'.$this->getRegex().'$#', $path, $matches);
        array_shift($matches);
        return $matches;
    }

	public function matchWith($path){
        /*echo $this->getPath().' ===> ';
        echo '#^'.$this->getRegex().'$#';
        echo ' VS ';
        echo $path;
        $found = preg_match('#^'.$this->getRegex().'$#', $path, $matches);
        var_dump($found);
        var_dump($matches);
        echo '<hr >';*/

        return preg_match('#^'.$this->getRegex().'$#', $path, $matches);
	}
	
	
}