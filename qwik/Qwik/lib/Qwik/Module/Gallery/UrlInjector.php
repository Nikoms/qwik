<?php

namespace Qwik\Module\Gallery;

use Imagine\Image\ImageInterface;
use Qwik\Cms\AppManager;
use Qwik\Cms\Page\PageNotFoundException;
use Qwik\Cms\Site\Site;
use Qwik\Component\Routing\Response;
use Qwik\Component\Template\TemplateProxy;

class UrlInjector implements \Qwik\Cms\Module\UrlInjector{

    /**
     * Pour l'ajout de route & autres (extend de twig) pour les thumbnails
     * @param AppManager $appManager
     * @param Site $site
     * @return mixed|void
     * @throws PageNotFoundException
     */
    public static function injectInApp(AppManager $appManager, Site $site){

        //Ajout d'un helper si on veut affichier le "titre" d'une image (en fonction de son nom)
        TemplateProxy::getInstance()->getTemplateEngine()->addFilter('toTitle', new \Twig_Filter_Function('Qwik\Module\Gallery\Gallery::toTitle'));


        //Ajout de l'url pour les thumbnails
        //Ex: /q/cache/gallery/120/80/85/images/bureau/Desert.jpg. {url} => images/bureau/Desert.jpg
        $appManager->getRouter()->get('gallery', '/' . $site->getVirtualUploadPath() . Gallery::WWW_THUMBNAIL_PATH . '/{url}', function($width, $height, $quality, $url) use ($appManager, $site) {

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
            $thumbPath = $site->getWww() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $site->getRealUploadPath() . Gallery::getThumbnailPathFor($width, $height, $quality) . '/'  . $pathOfFile);


            //On va copier l'originale... (si elle existe)
            $from = Gallery::getOriginalWwwPath($site, $url);
            if(file_exists($from)){

                //Création de l'objet thumbnail via Imagine (pas encore de création de fichier jpg à cet endroit)
                $thumbnail = $imagine->open($from)->thumbnail(new \Imagine\Image\Box($width, $height), ImageInterface::THUMBNAIL_OUTBOUND);

                //Si le cache est actif, alors on sauve, au bon endroit, en fichier physique
                if($appManager->getEnvironment()->get('module.gallery.cache')){

                    //Creation du path du thumbnail
                    if(!is_dir($thumbPath)){
                        mkdir($thumbPath, 0777, true);
                    }

                    //On sauve où les thumbnail ?!
                    $to = $thumbPath. DIRECTORY_SEPARATOR . $fileName;

                    //sauvegarde en fichier
                    $thumbnail->save($to, array('quality' => $quality));
                }


                //On renvoi gentillement l'image pour l'afficher
                $response = new Response();
                $response->setContent($thumbnail->get(pathinfo($fileName, \PATHINFO_EXTENSION)));
                $response->setFileName($fileName);
                return $response;

            }else{
                throw new PageNotFoundException();
            }
        })->assert('url', '.*');

    }

}