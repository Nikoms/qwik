<?php 
namespace Qwik\Kernel\Module\File\Entity;

use Qwik\Kernel\App\Module\Module;
use Qwik\Kernel\App\Language;



class File extends Module{

    public function getTemplateVars(){

        $fileContent = '';
        foreach($this->getFiles() as $file){
        	$fileContent .= $this->getFileContent($file);
        }

        $config = array(
            'fileContent' => $fileContent,
        );
        return $config;
    }

    private function getFiles(){
        $config = $this->getConfig();
        //si pas de fichier on renvoit un array vide
        if(empty($config['file'])){
            return array();
        }
        
        //On fait en sorte d'avoir un array de fichiers, même si on a qu'un fichier
        $files = is_array($config['file']) ? $config['file'] : array((string) $config['file']);
        $return = array();
        //Pour chaque fichier, on va faire des choses pour que ca marche en multilangue :)
        foreach($files as $file){
        	
        	//1. récup du nom du fichier (avec éventuellement des modifs suite à la langue)
        	$file = $this->getFile($file);
        	
        	//2. si c'est pas false, alors le fichier existe et on le rajoute dans l'array de return
        	if($file){
        		$return[] = $file;
        	}
        }
        
        return $return;

    }
    
    private function getFile($file){
    	
    	//On essaye d'avoir le fichier de la langue en cours
    	if($fullFileName = $this->getFileWithLangugage($file, Language::get())){
    		return $fullFileName;
    	}
    	//Sinon on essaye avec la langue par défaut
    	if($fullFileName = $this->getFileWithLangugage($file, Language::getDefault())){
    		return $fullFileName;
    	}
    	
    	return false;
    }
    
    private function getFileWithLangugage($file, $language){
    	
    	$site = $this->getZone()->getPage()->getSite();
        //Path où on va chercher les fichiers 
    	$path = str_replace('/', DIRECTORY_SEPARATOR, $site->getWww() . DIRECTORY_SEPARATOR . $site->getRealUploadPath() . DIRECTORY_SEPARATOR);
		
		//On garde la langue en cours
		$currentLanguage = Language::get();
		
		//On change la langue
		Language::changeIfPossible($language);
		
    	//Si on a une valeur fr,nl,en ca fonctionne :)
    	$file = Language::getValue($file);
        	
        //On transforme {language} en la langue en cours, comme ca c'est aussi dynamique sur le nom de fichier (ex: intro_{language}.html ou views/{language}/intro.html)
        $file = str_replace('{language}', Language::get(), $file);
        
        //On renvient à la langue courante
		Language::changeIfPossible($currentLanguage);
		
        //Si le fichier existe, ok on le renvoi
        if(file_exists($path . $file)){
        	return $path . $file;
        }
        //Le fichier n'existe pas
        return false;
        
        
    }

    private function getFileContent($file){
        switch(pathinfo($file, PATHINFO_EXTENSION)){
            case 'txt':
                return nl2br(file_get_contents($file));
                break;
            case 'php':
                ob_start();
                include $file;
                return ob_get_clean();
                break;
            default:
                return file_get_contents($file);
                break;
        }
    }

}
