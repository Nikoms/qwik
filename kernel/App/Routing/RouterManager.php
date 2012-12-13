<?php

namespace Qwik\Kernel\App\Routing;

class RouterManager {

	
	private $routes = array();
    //private $error;
	
	public function __construct(){
		
	}
	
	private function buildRoute($name, $path, $callable){
		$route = new Route();
		$route->setPath($path)
			->setName($name)
			->setCallable($callable);
		return $route;
	}
	
	public function post($name, $path, $callable){
		return $this->addRoute($this->buildRoute($name, $path, $callable)->addMethod('POST'));
	}
	public function get($name, $path, $callable){
		return $this->addRoute($this->buildRoute($name, $path, $callable)->addMethod('GET'));
	}
	public function match($name, $path, $callable){
		return $this->addRoute($this->buildRoute($name, $path, $callable)->addMethod('POST')->addMethod('GET'));
	}
    /*public function error($callable){
        $this->error = $callable;
    }
    public function getResponseError(\Exception $ex){
        //Si on a pas prévu les erreurs, on rajoute vite une fonction en schmet :)
        if(is_null($this->error)){
            $this->error = function(\Exception $ex, $code){
                return 'Error (' . $code . '): '.$ex->getMessage();
            };
        }

        return $this->getResponse($this->error, array($ex, $ex->getCode()));
    }*/
	
	public function addRoute(Route $route){
		$this->routes[] = $route;
		return $route;
	}
	public function getRoutes(){
		return $this->routes;
	}

    private function getResponse($callable, $vars){
        $content = call_user_func_array($callable, $vars);
        if($content instanceof Response){
            return $content;
        }
        $response = new Response();
        $response->setContent($content);
        return $response;
    }
	
	public function getResponseForUri($path){
        //Si c'est un slash, on remplace pas car sinon ca remplace tous les / :D
        if($this->getBaseUrl() !== '/'){
            $path = str_replace($this->getBaseUrl(), '', $path);
        }

		
		$route = $this->findRoute($path);


        if(!is_null($route)){
            return $this->getResponse($route->getCallable(), $route->getVarForPath($path));
        }

        throw new \Qwik\Kernel\App\Page\PageNotFoundException();
	}
	
	private function findRoute($path){
		foreach($this->getRoutes() as $route){
			if($route->matchWith($path)){
				return $route;
			}
		}
		return null;
	}
	
	
	
	public function getBaseUrl(){
		return dirname($_SERVER['SCRIPT_NAME']);
	}
}