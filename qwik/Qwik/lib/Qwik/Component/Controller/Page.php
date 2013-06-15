<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 29/05/13
 * Time: 23:58
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Component\Controller;


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
            return $app['qwik.page.service']->getOneByUrl($request->attributes->get('pageName'));
        };


        //J'ai une langue et une page :)
        $app->get('/{_locale}/{pageName}/', array($this, 'page'))
            ->convert('page', $callBackPage)
            ->bind('page')
            ->assert('_locale', '[a-z]{2}');



        return $controllers;
    }


    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    static public function getFirstPageRedirect(Application $app)
    {
        $firstPage = $app['qwik.page.service']->getFirst($app['site']);
        if($firstPage === null){
            return null;
        }
        return $app->redirect($app['url_generator']->generate('page', array('_locale' => $app['locale'], 'pageName' => $firstPage->getUrl())));
    }

    /**
     * Arrivée sur le site, j'attends une redirection vers la première page
     * @param Application $app
     * @param null|string $_locale Locale choisie (ou pas)
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function root(Application $app, $_locale = null)
    {
        $app['locale'] = $_locale !== null ? $_locale :Request::createFromGlobals()->getPreferredLanguage($app['site']->getLanguages());
        $page = Page::getFirstPageRedirect($app);
        if($page === null){
            $app->abort(404);
        }
        return $page;
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
            $app->abort(404);
        }
        return $app['twig']->render('templates/' . $page->getTemplate() . '/display.html.twig',
            array(
                'page' => $page
            )
        );
    }

}