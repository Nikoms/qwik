<?php
namespace Qwik\Kernel\App;

class AutoLoader {

	private static $namespaces = array(
		'Symfony\Component\Yaml' 				=> 'kernel/vendor/Yaml',
		'Qwik\Kernel'							=> 'kernel',
		'Imagine' 								=> 'kernel/vendor/Imagine/lib/Imagine',
	);

	private static function getDebugBackTraceLight(){
		$debugs = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($debugs as &$debug) {
			unset($debug['object']);
			unset($debug['args']);
			if(isset($debug['type'])){
				unset($debug['type']);
			}
		}
		array_shift($debugs);
		return array_values($debugs);
	}
	
	/**
	 * @static
	 * @param $className
	 */
	public static function autoload($className) {

		$filePath = self::getFilePath($className);
		$path = self::getRootDir() . $filePath . '.php';
		
		if($filePath === $className){
			self::errorDebug($className);
		}
		
		if (file_exists($path)) {
			include($path);
		} else {
			self::errorDebug($className, $path);
		}
	}
	
	private static function errorDebug($className, $path = ''){
		
		//Combien on a d'autoloader? Pcq si y'en a d'autres, peut-être que eux savent comment la classe se load?!
		$nbAutoloaders = count(spl_autoload_functions());
		//Si on appelle du twig ou swift, alors on by pass
		if(stripos($className, 'Twig_') === 0 || stripos($className, 'Swift_') === 0){
			return;
		}
		
		//Si on est ici, c'est qu'on avait qu'un seul autoloader et qu'on a rien trouvé...

		echo '<hr/><h1>AutoLoader</h1>
		<ul>
			<li>Class name: ' . $className . '</li>
			<li>';
		
		//Si on a pas de path c'est qu'on a meme pas trouvé de mappage avec un namespace
		if(empty($path)){
			echo 'No namespace has been mapped!';
		}else{
			echo 'No file found at ' . $path;
		}
		
		echo '</li>
		</ul>';
		
		$backtrace = self::getDebugBackTraceLight();		
		echo 'Called by file ' . $backtrace[2]['file'] . ', line ' . $backtrace[2]['line'] . '<hr/>';
		echo '<h2>Backtrace</h2><pre>' . print_r($backtrace, true) . '</pre>';		
		exit();
	}

	/**
	 * @static
	 * @param $className
	 * @return bool|string
	 */
	private static function getFilePath($className) {
        foreach(self::$namespaces as $nameSpace => $path){
            //Si on se trouve au début de la string, hop on remplace!
            //Si on trouve le namespace dans le nom de la classe au début
            if(strpos($className, $nameSpace) === 0){
                //Remplacement de $nameSpace par path via substr_replace (si on fait un str_replace, ca remplacement toutes les occurences, et c'est pas ca qu'on veut)
                return str_replace('\\', DIRECTORY_SEPARATOR ,substr_replace($className, $path, 0, strlen($nameSpace)));
            }
        }
        return '';
        //Ceci ne fonctionne pas pour Imagine
		//return strtr($className,self::$namespaces);
	}
	
	private static function getRootDir(){
		return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
	}

	public static function register() {
		spl_autoload_register(array(new AutoLoader(), 'autoload'));
	}

}

AutoLoader::register();

