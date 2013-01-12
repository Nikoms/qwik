<?php

namespace Qwik\Kernel\App\Routing;

/**
 * Gestionnaire de routes
 */
class RouterManager {

    /**
     * @var array Liste des routes
     */
    private $routes = array();

    private $baseUrl;

    /**
     * @param $baseUrl string
     */
    public function __construct($baseUrl){
		$this->baseUrl = (string) $baseUrl;
	}

    /**
     * Ajoute d'une route qui répond uniquement à une méthode POST
     * @param $name string Nom de la route
     * @param $path string chemin de la route
     * @param callable $callable fonction callback à appeler
     * @return Route
     */
    public function post($name, $path, $callable){
		return $this->addRoute($this->buildRoute($name, $path, $callable)->addMethod('POST'));
	}


    /**
     * Ajoute d'une route qui répond uniquement à une méthode GET
     * @param string $name Nom de la route
     * @param string $path chemin de la route
     * @param callable $callable callback à appeler
     * @return Route
     */
    public function get($name, $path, $callable){
		return $this->addRoute($this->buildRoute($name, $path, $callable)->addMethod('GET'));
	}

    /**
     * Ajoute d'une route qui répond à une méthode GET et POST
     * @param string $name Nom de la route
     * @param string $path chemin de la route
     * @param callback $callable callback à appeler
     * @return Route
     */
	public function match($name, $path, $callable){
		return $this->addRoute($this->buildRoute($name, $path, $callable)->addMethod('POST')->addMethod('GET'));
	}

    /**
     * Ajout d'une route dans le manager
     * @param Route $route Route à ajouter
     * @return Route
     */
    public function addRoute(Route $route){
		$this->routes[$route->getName()] = $route;
		return $route;
	}

    /**
     * Renvoi la liste des routes gérées par le manager
     * @return Route[]
     */
    public function getRoutes(){
		return $this->routes;
	}

    /**
     * Récupère un object Response en fonction du $path ou lance une exception si aucune route n'a renvoyé de resultat
     * @param $path
     * @return Response
     * @throws \Qwik\Kernel\App\Page\PageNotFoundException
     */
    public function getResponseForUri($uri){
        $uri = trim((string) $uri);

        //Si l'index.php n'est pas à la racine du site, alors on enlève tout ce qui se trouve avant.
        //Ceci afin de tester les routes dans les meilleures conditions, c'est à dire "A partir du dossier où se trouve index.php"
        if($this->getBaseUrl() !== '/'){
            $uri = str_replace($this->getBaseUrl(), '', $uri);
        }

        //On prend pas ce qu'il y a après ? ou #
        $uri = substr($uri, 0, strcspn($uri, '?#'));
        
        //On trouve (peut-être) la route en fonction du uri demandé
		$route = $this->findRoute($uri);

        //Si on a pas trouvé de route, on lance une exception (404)
        if(is_null($route)){
            throw new \Qwik\Kernel\App\Page\PageNotFoundException();
        }

        //Enfin, tout va bien, on a un Response
        return $this->getResponse($route->getCallable(), $route->getVarForPath($uri));

	}

    /**
     * Renvoi la première route qui correspond au pattern du path. Si aucune route n'a été trouvée, on renvoi null
     * @param $path string
     * @return null|Route
     */
    private function findRoute($uri){

        $uri = (string) $uri;
        //Pour chaque route
		foreach($this->getRoutes() as $route){
            //Regarde sur l'uri correspond à la route
			if($route->matchWith($uri)){
				return $route;
			}
		}
		return null;
	}


    /**
     * Appel de la méthode callback, avec les arguments et transformation en Response si ce n'est pas déjà fait
     * @param $callable fonction à app
     * @param $vars array
     * @return Response
     */
    private function getResponse($callable, array $vars){
        $content = call_user_func_array($callable, $vars);

        //Si c'est déjà une instance de Response, c'est bon, la fonction à fait son job
        if($content instanceof Response){
            return $content;
        }
        //Si ce n'est pas un response, alors on en crée un et on met le retour de la fonction comme étant le contenu (string)
        $response = new Response();
        $response->setContent($content);
        return $response;
    }


    /**
     * Récupère la base du site
     * @return string
     */
    public function getBaseUrl(){
        return $this->baseUrl;
		//return dirname($_SERVER['SCRIPT_NAME']);
	}


    /**
     * Construit une route
     * @param $name Nom de la route
     * @param $path chemin de la route
     * @param $callable fonction à appelé pour récupérer le contenu
     * @return Route
     */
    private function buildRoute($name, $path, $callable){
        $route = new Route();
        $route->setPath($path)
            ->setName($name)
            ->setCallable($callable);
        return $route;
    }
}