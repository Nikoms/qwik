<?php
namespace Qwik\Kernel\App;

/**
 * Autoloader des classes
 */
class AutoLoader {

    /**
     * @var array Tableaux des namespaces gérés, avec leur chemin vers le bon dossier
     */
    private static $namespaces = array(
		'Symfony\Component\Yaml' 				=> 'kernel/vendor/Yaml',
		'Qwik\Kernel'							=> 'kernel',
		'Imagine' 								=> 'kernel/vendor/Imagine/lib/Imagine',
	);

    /**
     * @return array Debug backtrace light pour pas exploser la mémoire
     */
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
     * Loading de la classe
	 * @static
	 * @param string $className
	 */
	public static function autoLoad($className) {


        //Exception: si on appelle du twig ou swift, alors on by pass car ils ont leur propre auto loader
        if(stripos($className, 'Twig_') === 0 || stripos($className, 'Swift_') === 0){
            return true;
        }

        $className = (string) $className;
        //Récupération du path du fichier de la classe
		$filePath = self::getFilePath($className);

        //Si le path n'est pas géré, on recoit false, donc on log
		if($filePath === false){
			self::errorDebug($className);
            return false;
		}

        $path = self::getRootDir() . $filePath . '.php';

        //Si le fichier existe on l'include, sinon on log l'erreur
		if (file_exists($path)) {
			include($path);
            return true;
		} else {
			self::errorDebug($className, $path);
            return false;
		}
	}

    /**
     * Log d'un message d'erreur
     * @param $className
     * @param string $path
     */
    private static function errorDebug($className, $path = ''){

        $message = 'Class name: ' . $className . ' not found.';
        if(empty($path)){
            $message .= ' No namespace has been mapped!';
        }else{
            $message .= ' No file found at ' . $path . '!';
        }
        //Warning lorsqu'on trouve pas la classe
        \Qwik\Kernel\Log\Logger::getInstance()->warning($message);
	}

	/**
     * Récupération du path présumé du fichier de la classe
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
        return false;
        //Ceci ne fonctionne pas pour Imagine, sinon c'aurait été cool
		//return strtr($className,self::$namespaces);
	}

    /**
     * @return string Le "root" où se trouve les classes
     */
    private static function getRootDir(){
		return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
	}

    /**
     * Register de l'autoload
     */
    public static function register() {
		spl_autoload_register(array(new AutoLoader(), 'autoLoad'));
	}

}
//On registre l'autoload
AutoLoader::register();

