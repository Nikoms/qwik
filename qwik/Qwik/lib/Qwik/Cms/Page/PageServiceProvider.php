<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 15/06/13
 * Time: 20:57
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Cms\Page;


use Silex\Application;
use Silex\ServiceProviderInterface;

class PageServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['qwik.page.service'] = $app->share(function ($app) {
            return new PageService($app['site'], $app['qwik.path']['site']['pages']);
        });
    }

    public function boot(Application $app)
    {
    }

}