<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 20/05/13
 * Time: 23:19
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Gallery;


use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Imagick\Imagine as ImagineMagick;
use Imagine\Gd\Imagine as ImagineGd;
use Qwik\Cms\Site\Site;
use Silex\Application;

class File {
    private $www;
    private $site;

    public function __construct($www, Site $site){
        $this->www = $www;
        $this->site = $site;
    }

    public function getOriginalPath($path){
        return $this->www . '/' . $this->site->getRealUploadPath() . '/' . $path;
    }

    public function getFiles($path){


        if(!is_dir($this->getOriginalPath($path))){
            return array();
        }

        $return = array();
        foreach (scandir($this->getOriginalPath($path)) as $file) {
            if (!is_dir($file) && $file != "." && $file != "..") {
                //On met bien un "/" et pas un directory_separator, car c'est destiné pour le html
                $return[] = $path . '/' .$file;
            }
        }
        return $return;
    }

    /**
     * Crée le thumb pour l'url donné avec les params en plus. Renvoi le path vers le thumbnail
     * @param $url
     * @param $width
     * @param $height
     * @param $quality
     * @return string
     */
    public function createThumb($url, $width, $height, $quality){

        //Si j'ai pas imagick, alors je prends GD (GD = Problème de mémoire pour les grosses images)
        if (TRUE !== extension_loaded('imagick')){
            $imagine = new ImagineGd();
        }else{
            $imagine = new ImagineMagick();
        }

        //Création de l'objet thumbnail via Imagine (pas encore de création de fichier jpg à cet endroit)
        $thumbnail = $imagine->open($this->getOriginalPath($url))->thumbnail(new Box($width, $height), ImageInterface::THUMBNAIL_OUTBOUND);


        //on Calcule le path www du cache (thumbnail)
        $thumbPath = $this->www
            . DIRECTORY_SEPARATOR
            . str_replace('/',
                DIRECTORY_SEPARATOR,
                //Path vers le dossier du site + dossier spécial pour les thumbnails
                $this->site->getRealUploadPath() . Gallery::getThumbnailPathFor($width, $height, $quality))
            . DIRECTORY_SEPARATOR
            . pathinfo($url, PATHINFO_DIRNAME);


        //Creation du path du thumbnail
        if(!is_dir($thumbPath)){
            mkdir($thumbPath, 0777, true);
        }

        //On sauve où les thumbnail ?!
        $thumbnailFilePath = $thumbPath. DIRECTORY_SEPARATOR . pathinfo($url, PATHINFO_BASENAME);

        //sauvegarde en fichier
        $thumbnail->save($thumbnailFilePath, array('quality' => $quality));

        return $thumbnailFilePath;

    }
}