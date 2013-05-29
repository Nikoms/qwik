<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 29/05/13
 * Time: 23:58
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Component\Routing;



use Qwik\Cms\Page\PageManager;
use Qwik\Cms\Page\PageNotFoundException;
use Silex\Application;
use Silex\ControllerProviderInterface;

class Controller implements ControllerProviderInterface{

    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function getFirstPageRedirect(Application $app){
        $pageManager = new PageManager();
        $firstPage = $pageManager->findFirst($app['site']);
        return $app->redirect($app['url_generator']->generate('page', array('_locale' => $app['locale'],'pageName' => $firstPage->getUrl())));
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];


        $controller = $this;

        $app->get('/home', function() use ($app){
        })->bind('home');


        //Arrivée sur le site, j'attends une redirection vers la première page
        $app->get('/', function() use ($app, $controller){
            return $controller->getFirstPageRedirect($app);
        })->bind('root');

        //J'ai choisi une langue, mais pas de page, j'attends une redirection vers la première page
        $app->get('/{_locale}/', function($_locale) use($app, $controller){
            //Changement de la langue quand c'est possible...
            $app['locale'] = $app['qwik.locale']->changeIfPossible($_locale);
            return $controller->getFirstPageRedirect($app);
        })->bind('root_language')->assert('_locale','[a-z]{2}');


        //J'ai une langue et une page :)
        $app->get('/{_locale}/{pageName}/', function($_locale, $pageName) use($app){
            //Changement de la langue quand c'est possible...
            $app['locale'] = $app['qwik.locale']->changeIfPossible($_locale);
            $app['translator']->setLocale($app['locale']);

            $pageManager = new PageManager();
            $page = $pageManager->findOneByUrl($app['site'], $pageName);

            //Si pas de page, alors 404
            if(!$page){
                throw new PageNotFoundException();
            }
            return $app['twig']->render('templates/' . $page->getTemplate() . '/display.html.twig',
                array(
                    'page' => $page
                ));

        })->bind('page')->assert('_locale','[a-z]{2}');

        //Clear varnish et les templates
        $app->get('/admin/cc', function() use($app, $controller){
            //Clear Vanish, que si on a curl init
            if(function_exists('curl_init')){
                //Clear varnish
                header("Cache-Control: max-age=1"); // don't cache ourself
                //error_reporting(E_ALL);
                //ini_set("display_errors", 1);
                // Set to true to hide varnish result
                define("SILENT", false);
                if ( $ch = curl_init("http://" .$app['site']->getDomain() . "/") ) {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PURGE");
                    curl_setopt($ch, CURLOPT_NOBODY, SILENT);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }

            //Clear du template
            $app['twig']->clearCacheFiles();

            return $controller->getFirstPageRedirect($app);
        });

        $this->addModulesRoutes($app);


        return $controllers;
    }



    private function addModulesRoutes(Application $app){
        $modules = $app['qwik.env']->get('modules', array());
        foreach(array_keys($modules) as $moduleName){
            $controller = $app['qwik.module']->getController($moduleName);
            $app->mount('/module/'.$moduleName.'/', $controller);
        }
    }
}