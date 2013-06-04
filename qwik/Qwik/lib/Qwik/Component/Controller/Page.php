<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 29/05/13
 * Time: 23:58
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Component\Controller;


use Qwik\Cms\Page\PageManager;
use Qwik\Cms\Page\PageNotFoundException;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class Page implements ControllerProviderInterface
{


    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];


        //Arrivée sur le site, j'attends une redirection vers la première page
        $app->get('/', array($this, 'root'))->bind('root');

        //J'ai choisi une langue, mais pas de page, j'attends une redirection vers la première page
        $app->get('/{_locale}/', array($this, 'root'))->bind('root_language')->assert('_locale', '[a-z]{2}');


        //$page est nécessaire
        $callBackPage = function ($page, Request $request) use ($app) {
            $pageManager = new PageManager();
            return $pageManager->findOneByUrl($app['site'], $request->attributes->get('pageName'));
        };


        //J'ai une langue et une page :)
        $app->get('/{_locale}/{pageName}/', array($this, 'page'))
            ->convert('page', $callBackPage)
            ->bind('page')
            ->assert('_locale', '[a-z]{2}');


        //Ajout des routes des modules
        $this->addModulesRoutes($app);


        return $controllers;
    }


    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    static public function getFirstPageRedirect(Application $app)
    {
        $pageManager = new PageManager();
        $firstPage = $pageManager->findFirst($app['site']);
        return $app->redirect($app['url_generator']->generate('page', array('_locale' => $app['locale'], 'pageName' => $firstPage->getUrl())));
    }

    /**
     * Arrivée sur le site, j'attends une redirection vers la première page
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function root(Application $app)
    {
        return Page::getFirstPageRedirect($app);
    }

    /**
     * On a une page
     * @param Application $app
     * @param Request $request
     * @param $page
     * @return mixed
     * @throws \Qwik\Cms\Page\PageNotFoundException
     */
    public function page(Application $app, Request $request, $page)
    {
        //Changement de la langue quand c'est possible...
        $app['translator']->setLocale($request->getLocale());


        //Si pas de page, alors 404
        if (!$page) {
            throw new PageNotFoundException();
        }
        return $app['twig']->render('templates/' . $page->getTemplate() . '/display.html.twig',
            array(
                'page' => $page
            )
        );
    }


    /**
     * @param Application $app
     */
    private function addModulesRoutes(Application $app)
    {
        foreach (array_keys($app['qwik.modules']) as $moduleName) {
            $controller = $app['qwik.module']->getController($moduleName);
            $app->mount('/module/' . $moduleName . '/', $controller);
        }
    }
}