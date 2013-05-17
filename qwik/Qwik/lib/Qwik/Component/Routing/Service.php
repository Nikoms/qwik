<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 1/05/13
 * Time: 18:06
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Component\Routing;


use Qwik\Application;
use Qwik\Cms\Page\PageManager;
use Qwik\Cms\Page\PageNotFoundException;
use Qwik\Component\Locale\Language;

class Service {

    static public function addRoutes(Application $app){

        $silex = $app->getSilex();
        //Arrivée sur le site, j'attends une redirection vers la première page
        $app->getSilex()->get('/', function() use ($app){
            return Service::getFirstPageResponse($app);
        })->bind('root');

        //J'ai choisi une langue, mais pas de page, j'attends une redirection vers la première page
        $app->getSilex()->get('/{_locale}/', function($_locale) use($app, $silex){
            //Changement de la langue quand c'est possible...
            $silex['locale'] = Language::changeIfPossible($_locale);
            return Service::getFirstPageResponse($app);
        })->bind('root_language')->assert('_locale','[a-z]{2}');


        //J'ai une langue et une page :)
        $app->getSilex()->get('/{_locale}/{pageName}/', function($_locale, $pageName) use($app,$silex){
            //Changement de la langue quand c'est possible...
            $silex['locale'] = Language::changeIfPossible($_locale);
            $silex['translator']->setLocale($silex['locale']);

            $pageManager = new PageManager();
            $page = $pageManager->findOneByUrl($silex['site'], $pageName);

            //Si pas de page, alors 404
            if(!$page){
                throw new PageNotFoundException();
            }
            return $silex['twig']->render('templates/' . $page->getTemplate() . '/display.html.twig',
                array(
                    'page' => $page
                ));

        })->bind('page')->assert('_locale','[a-z]{2}');

        //Clear varnish et les templates
        $app->getSilex()->get('/admin/cc', function() use($app, $silex){
            //Clear Vanish, que si on a curl init
            if(function_exists('curl_init')){
                //Clear varnish
                header("Cache-Control: max-age=1"); // don't cache ourself
                //error_reporting(E_ALL);
                //ini_set("display_errors", 1);
                // Set to true to hide varnish result
                define("SILENT", false);
                if ( $ch = curl_init("http://" .$silex['site']->getDomain() . "/") ) {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PURGE");
                    curl_setopt($ch, CURLOPT_NOBODY, SILENT);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }

            //Clear du template
            $silex['twig']->clearCacheFiles();

            return Service::getFirstPageResponse($app);
        });

        //On va voir si les modules on des routes
        self::addModulesRoutes($app);
    }

    static private function addModulesRoutes(Application $app){
        $silex = $app->getSilex();
        $modules = $silex['env']->get('modules', array());
        foreach(array_keys($modules) as $moduleName){
            $silex['qwik_module']->getController($moduleName)->injectUrl();
        }
    }

    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws PageNotFoundException
     */
    static public function getFirstPageResponse(Application $app){

        $silex = $app->getSilex();
        //Récupération de la première page
        $pageManager = new PageManager();
        $firstPage = $pageManager->findFirst($silex['site']);

        //Si pas de page, alors 404
        if(!$firstPage){
            throw new PageNotFoundException();
        }
        return $silex->redirect('/' . Language::get() . '/' . $firstPage->getUrl());
    }
}