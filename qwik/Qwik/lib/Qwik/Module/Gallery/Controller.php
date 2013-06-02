<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:31
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Gallery;


use Silex\Application;
use Silex\ControllerProviderInterface;

class Controller implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        //Ajout d'un helper si on veut affichier le "titre" d'une image (en fonction de son nom)
        $app['twig']->addFilter('toTitle', new \Twig_Filter_Function(function ($fileName) {
            $title = ucfirst(pathinfo($fileName, PATHINFO_FILENAME));
            //Remplacement des _ par des espaces
            return str_replace('_', ' ', $title);
        }));

        //Ajout de l'url pour les thumbnails
        //TODO: Utiliser $controllers->get et pas $app->get, mais ca nécessite un petit refactoring car $controllers commence par /module/gallery
        //Ex: /q/cache/gallery/120/80/85/images/bureau/Desert.jpg. {url} => images/bureau/Desert.jpg
        $app->get('/' . $app['site']->getVirtualUploadPath() . Gallery::WWW_THUMBNAIL_PATH . '/{url}', function ($width, $height, $quality, $url) use ($app) {

            //Pour les espaces, et autres caractères bizarres (ex: photo (2).jpg posait problème car les espaces étaient remplacés par des %20)
            $url = urldecode($url);

            //Si l'originale existe, on va en faire un thumbnail
            if (file_exists($app['qwik.module.gallery.file']->getOriginalPath($url))) {
                $thumbnailFilePath = $app['qwik.module.gallery.file']->createThumb($url, $width, $height, $quality);
                //On renvoi gentillement l'image pour l'afficher
                $stream = function () use ($thumbnailFilePath) {
                    readfile($thumbnailFilePath);
                };
                //TODO content-type à changer en fonction du type de l'image
                return $app->stream($stream, 200, array('Content-Type' => 'image/png'));
            } else {
                return $app->abort(404, 'The image was not found.');
            }
        })->assert('url', '.*')->bind('module_gallery');

        return $controllers;
    }
}