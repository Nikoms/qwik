<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:31
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Gallery;


use Qwik\Cms\Module\Info;
use Silex\Application;

class Controller extends \Qwik\Cms\Module\Controller{

    /**
     * @param Info $info
     * @return Gallery
     */
    protected function getModule(Info $info){
        return new Gallery($info);
    }

    public function injectUrl(){

        $silex = $this->getApplication();
        //Ajout d'un helper si on veut affichier le "titre" d'une image (en fonction de son nom)
        $silex['twig']->addFilter('toTitle', new \Twig_Filter_Function('Qwik\Module\Gallery\Gallery::toTitle'));


        //Ajout de l'url pour les thumbnails
        //Ex: /q/cache/gallery/120/80/85/images/bureau/Desert.jpg. {url} => images/bureau/Desert.jpg
        $silex->get('/' . $silex['site']->getVirtualUploadPath() . Gallery::WWW_THUMBNAIL_PATH . '/{url}', function($width, $height, $quality, $url) use ($silex) {

            //Pour les espaces, et autres caractères bizarres (ex: photo (2).jpg posait problème car les espaces étaient remplacés par des %20)
            $url = urldecode($url);

            //Si j'ai pas imagick, alors je prends GD (GD = Problème de mémoire pour les grosses images)
            if (TRUE !== extension_loaded('imagick')){
                $imagine = new \Imagine\Gd\Imagine();
            }else{
                $imagine = new \Imagine\Imagick\Imagine();
            }

            //Récupération du nom du fichier
            $pathOfFile = pathinfo($url, PATHINFO_DIRNAME);

            //Récupération du path
            $fileName = pathinfo($url, PATHINFO_BASENAME);

            //on Calcule le path www du cache (thumbnail)
            $thumbPath = $silex['site']->getWww() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $silex['site']->getRealUploadPath() . Gallery::getThumbnailPathFor($width, $height, $quality) . '/'  . $pathOfFile);


            //On va copier l'originale... (si elle existe)
            $originalPath = Gallery::getOriginalWwwPath($silex['site'], $url);
            if(file_exists($originalPath)){

                //Création de l'objet thumbnail via Imagine (pas encore de création de fichier jpg à cet endroit)
                $thumbnail = $imagine->open($originalPath)->thumbnail(new \Imagine\Image\Box($width, $height), ImageInterface::THUMBNAIL_OUTBOUND);

                //Si le cache est actif, alors on sauve, au bon endroit, en fichier physique
                //if($silex['env']->get('module.gallery.cache')){

                //Creation du path du thumbnail
                if(!is_dir($thumbPath)){
                    mkdir($thumbPath, 0777, true);
                }

                //On sauve où les thumbnail ?!
                $thumbnailPath = $thumbPath. DIRECTORY_SEPARATOR . $fileName;

                //sauvegarde en fichier
                $thumbnail->save($thumbnailPath, array('quality' => $quality));
                //}

                //On renvoi gentillement l'image pour l'afficher
                $stream = function () use ($thumbnailPath) {
                    readfile($thumbnailPath);
                };
                //TODO content-type à changer en fonction du type de l'image
                return $silex->stream($stream, 200, array('Content-Type' => 'image/png'));
            }else{
                return $silex->abort(404, 'The image was not found.');
            }
        })->assert('url', '.*')->bind('module_gallery');
    }
}