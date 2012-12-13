<?php
namespace Qwik\Kernel\App;

class AutoLoader {

	private static $namespaces = array(
		'Symfony\Component\Yaml' 				=> 'kernel/vendor/Yaml',
		'Qwik\Kernel'							=> 'kernel/',
		'Imagine' 								=> 'kernel/vendor/Imagine/lib/Imagine',
	);

	private static function getDebugBackTraceLight(){
		$debugs = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($debugs as &$debug) {
			unset($debug['object']);
			unset($debug['args']);
		}
		array_shift($debugs);
		return array_values($debugs);
	}
	
	/**
	 * @static
	 * @param $className
	 */
	public static function autoload($className) {

		$path = self::getPath($className);
		if ($path) {
			$path = $path;
			if (file_exists($path)) {
				include($path);
			} else {
				echo '<hr/>AutoLoader<br />' . $path . ' (' . $className . ') introuvable... <br />';
				$backtrace = self::getDebugBackTraceLight();
				echo '<pre>' . print_r($backtrace, true) . '</pre>';				
				echo 'Appelé par le fichier ' . $backtrace[1]['file'] . ' à la ligne ' . $backtrace[1]['line'] . '<hr/>';
				exit();
			}
		}
	}

	/**
	 * @static
	 * @param $className
	 * @return bool|string
	 */
	private static function getPath($className) {
		//On cherche le dernier \ qui va nous permettre de savoir le namespace et la classe
		$pos = strrpos($className, '\\');
		
		$namespace = substr($className, 0, $pos);
		$pathFounded = '';

		foreach(self::$namespaces as $ns => $path){
			if(strpos($namespace, $ns) !== false){
				
				$pathFounded = $path . DIRECTORY_SEPARATOR;
				//Si on a un namespace plus long que celui dans l'array, alors on ajoute des choses au path
				if(strlen($ns) !== strlen($namespace)){
					//Je transforme les \ en /
					$namespace = str_replace('\\','/',$namespace);
					//strlen($ns)+1 est le d�bug ou commence la diff�rence. En d'autres mots: C'est � partir de l� qu'on va ajouter le reste du namespace au path
					$pathFounded .= substr($namespace, strlen($ns)+1). DIRECTORY_SEPARATOR;
				}
				break;
			}
		}
		if (empty($pathFounded)) {
			
			$nbAutoloaders = count(spl_autoload_functions());
			//Si on a plus qu'un autoloader, ca veut dire qu'on en autra d'autres plus tard :)... Donc on exit
			if($nbAutoloaders == 1){
				$backtrace = self::getDebugBackTraceLight();
				exit('<hr/>AutoLoader:<br />
					La classe ' . $className . ' n\'a pas �t� trouv�e: <br />
					<b>' . $namespace . '</b> ne correspond � aucun Namespace mapp�.<br />
					Appel� par le fichier ' . $backtrace[2]['file'] . ' � la ligne ' . $backtrace[2]['line'] . '<br />
					Liste des namespaces:
				<pre>' . print_r(array_keys(self::$namespaces), true) . '</pre><hr/>');
			}
			return false;
		}
		
		return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $pathFounded . substr($className, $pos + 1) . '.php';
	}

	public static function register() {
		spl_autoload_register(array(new AutoLoader(), 'autoload'));
	}

}

AutoLoader::register();

