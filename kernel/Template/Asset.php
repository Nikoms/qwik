<?php

namespace Qwik\Kernel\Template;

class Asset{

    public static function getPathOfAsset($uri){
        //si j'ai un cache alors on renvoi l'uri directement
        if(\Qwik\Kernel\App\AppManager::getInstance()->getEnvironment()->get('template.cache', false)){
            return $uri;
        }

        return \Qwik\Kernel\App\AppManager::getInstance()->getBaseUrl() . $uri;
        //Si on est ici, on demande une ressource dans le dossier "public"

    }

    public static function getFullPath($uri){

        //si j'ai un cache alors je ne devrais pas être ici!
        if(\Qwik\Kernel\App\AppManager::getInstance()->getEnvironment()->get('template.cache', false)){
            return null;
        }

        $virtualPath = '/' . \Qwik\Kernel\App\AppManager::getInstance()->getSite()->getVirtualUploadPath();

        if(strpos($uri, $virtualPath) === 0){ // Le fichier demandé est "url rewrité", donc on va prendre dans le dossier ressource
            $fullPathOfFile = \Qwik\Kernel\App\AppManager::getInstance()->getSite()->getPath() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . substr($uri, strlen($virtualPath)) ;
        }else{ //Si c'est pas un url rewrité alors on va dans le dossier WWW
            $fullPathOfFile = \Qwik\Kernel\App\AppManager::getInstance()->getSite()->getWww() . $uri;
        }
        //changement ds / en \
        return str_replace('/', DIRECTORY_SEPARATOR, $fullPathOfFile);
    }

    public static function getResponseOfAsset($uri){

        $fullPathOfFile = self::getFullPath($uri);

        switch(strtolower(pathinfo($uri, PATHINFO_EXTENSION))){
            //Liste des extensions autorisées à être lues (switch plus rapide que in_array)
            case 'js':
            case 'css':
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'png':
            case 'txt':
            case 'html':
            case 'htm':
            case 'doc':
            case 'docx':
            case 'xls':
            case 'xlsx':
            case 'ppt':
            case 'pptx':
            case 'csv':
            case 'pdf':
                //Si le fichier existe, on va le chercher, sinon on passera au "default" qui renvoi null
                if(file_exists($fullPathOfFile)){
                    $response = new \Qwik\Kernel\App\Routing\Response();
                    $response->setContent(file_get_contents($fullPathOfFile));
                    $response->setFileName($fullPathOfFile);
                    return $response;
                }
            //break; #nobreak!
            default:
                return null;
        }
    }
}