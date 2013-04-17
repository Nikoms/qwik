<?php
namespace Qwik\App;

/**
 * Class AutoLoader
 * @package Qwik\App
 */
class AutoLoader {

	/**
     * Loading de la classe
	 * @static
	 * @param string $className
	 */
    public static function autoLoad($className) {

        if (0 !== strpos($className, 'Qwik')) {
            return;
        }

        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR , $className) . '.php';

		if (is_file($path)) {
			include($path);
		}
	}

    /**
     * Register de l'autoload
     */
    public static function register() {
		spl_autoload_register(array(new AutoLoader(), 'autoLoad'));
	}

}
AutoLoader::register();


