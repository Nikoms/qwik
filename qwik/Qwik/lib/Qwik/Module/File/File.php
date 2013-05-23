<?php 
namespace Qwik\Module\File;

use Qwik\Cms\Module\Module;
use Qwik\Component\Locale\Locale;
use Qwik\Module\File\Type\Content;
use Qwik\Module\File\Type\Twig;


/**
 * Module "File" qui permet des gérer l'affichage de fichier(s)
 */
class File extends Module{


    /**
     * @return array Tableau des fichiers à afficher
     */
    public function getFiles(Locale $locale){
        $file = $this->getInfo()->getConfig()->get('config.file', array());
        //si pas de fichier on renvoit un array vide
        if(empty($file)){
            return array();
        }
        
        //On fait en sorte d'avoir un array de fichiers, même si on a qu'un fichier
        $files = (array) $file;
        $return = array();

        //Pour chaque fichier, on va faire des choses pour que ca marche en multilangue :)
        foreach($files as $file){

        	//1. récup du nom du fichier (avec éventuellement des modifs suite à la langue)
        	$file = $this->getFilePath($file, $locale);

        	//2. si c'est pas false, alors le fichier existe et on le rajoute dans l'array de return
        	if($file !== false){
        		$return[] = $this->getFile($file);
        	}
        }
        
        return $return;

    }

    /**
     * @param $file
     * @return bool|string Renvoi le path absolu du fichier (peut changer en fonction de la langue) ou FALSE si on a pas trouvé de fichier
     */
    private function getFilePath($file, Locale $locale){

    	//On essaye d'avoir le fichier de la langue en cours
    	if($fullFileName = $this->getFileWithLanguage($file, $locale, $locale->get())){
    		return $fullFileName;
    	}

        //Si on arrive ici, c'est que:
        //  - soit on avait pas de fichier avec la langue en cours,
        //  - soit on avait bien un fichier DANS LA CONFIG, mais on ne l'a pas trouvé à l'endroit spécifié. Dans ce cas là, on va essayer avec la langue par défaut du site...


    	//Sinon on essaye avec la langue par défaut
    	if($fullFileName = $this->getFileWithLanguage($file, $locale, $locale->getDefault())){
    		return $fullFileName;
    	}
    	
    	return false;
    }

    /**
     * Renvoi le chemin vers le fichier selon la langue. Si le fichier n'existe pas, on renvoi false.
     * @param $file
     * @param Locale $locale
     * @param string $language
     * @return bool|string
     */
    private function getFileWithLanguage($file, Locale $locale, $language){

    	//Si on a une valeur fr,nl,en ca fonctionne :)
    	$file = $locale->getValue($file, $language);
        	
        //On transforme {language} en la langue en cours, comme ca c'est aussi dynamique sur le nom de fichier (ex: intro_{language}.html ou views/{language}/intro.html)
        $file = str_replace('{language}', $language, $file);



        $filePath = $this->getPath() . str_replace('/', DIRECTORY_SEPARATOR, $file) ;
        //Si le fichier existe, ok on le renvoi
        if(file_exists($filePath)){
        	return $filePath;
        }
        //Le fichier n'existe pas
        return false;
        
        
    }

    /**
     * Path où on va chercher les fichiers
     */
    private function getPath(){
        return $this->getInfo()->getZone()->getPage()->getSite()->getPath() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $file Chemin du fichier
     * @return \Qwik\Module\File\Type\File
     */
    private function getFile($file){
        $file = (string) $file;
        switch(pathinfo($file, PATHINFO_EXTENSION)){
            case 'txt':
                return new Content(nl2br(file_get_contents($file)));
                break;
            case 'php':
                //$module pourra être utilisé par l'include
                $phpToString = function($module) use ($file){
                    ob_start();
                    include $file;
                    return new Content(ob_get_clean());
                };
                //On passe $this, même si je sais qu'on peut accéder à this dans la méthode anonyme. C'est plus clair dans le ".php" d'utilise $module plutot que $this
                return $phpToString($this);
                break;
            case 'twig':
                return new Twig(str_replace($this->getPath(), '', $file));
                break;
            default:
                return new Content(file_get_contents($file));
                break;
        }
    }

}
