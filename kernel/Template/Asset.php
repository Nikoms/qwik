<?php

namespace Qwik\Kernel\Template;

class Asset{

    /**
     * Renvoi le path de l'asset. Pour le moment, peu importe qu'on soit en dev/prod, on renvoi toujours l'url "directe"
     * @param $uri
     * @return string
     */
    public static function getPathOfAsset($uri){
        $pathInfo = pathinfo(\Qwik\Kernel\App\AppManager::getInstance()->getBaseUrl(), PATHINFO_DIRNAME);
        //On remplace les \ par des / et on enlève le slash à la fin, car normalement uri doit commencer par un /
        $pathInfo = rtrim(str_replace('\\', '/', $pathInfo), '/');
        return $pathInfo . $uri;
    }
/*
    public static function getFullPath($uri){

        //si j'ai un cache alors je ne devrais pas être ici!
        if(\Qwik\Kernel\App\AppManager::getInstance()->getEnvironment()->get('template.cache', false)){
            return null;
        }

        $virtualPath = '/' . \Qwik\Kernel\App\AppManager::getInstance()->getSite()->getVirtualUploadPath();

        if(strpos($uri, $virtualPath) === 0){ // Le fichier demandé est "url rewrité", donc on va prendre dans le dossier ressource
            $fullPathOfFile = \Qwik\Kernel\App\AppManager::getInstance()->getSite()->getWww() . DIRECTORY_SEPARATOR . \Qwik\Kernel\App\AppManager::getInstance()->getSite()->getRealUploadPath() . substr($uri, strlen($virtualPath)) ;
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
    }*/
}