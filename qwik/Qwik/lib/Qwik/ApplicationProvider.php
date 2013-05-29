<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 29/05/13
 * Time: 23:36
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik;


use Silex\Application;
use Silex\ServiceProviderInterface;

class ApplicationProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['qwik'] = $app->share(function ($app) {
            return new \Qwik\Application($app);
        });
    }

    public function boot(Application $app)
    {
        //Ceci permet d'initialiser qwik et donc les routes :)
        $app['qwik']->init();
    }
}
