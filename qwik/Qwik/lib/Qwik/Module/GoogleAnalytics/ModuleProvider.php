<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:31
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\GoogleAnalytics;


use Silex\Application;
use Silex\ServiceProviderInterface;

class ModuleProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['qwik.module.google.analytics'] = $app->share(function ($app) {
            return new Module($app);
        });
    }

    public function boot(Application $app)
    {
    }

}