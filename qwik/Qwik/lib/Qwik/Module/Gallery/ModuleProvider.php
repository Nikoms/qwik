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
use Silex\ServiceProviderInterface;

class ModuleProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['qwik.module.gallery'] = $app->share(function ($app) {
            return new Module($app);
        });

        $app['qwik.module.gallery.file'] = $app->share(function ($app) {
            return new File($app['qwik']->getWww(), $app['site']);
        });
    }

    public function boot(Application $app)
    {
    }


}