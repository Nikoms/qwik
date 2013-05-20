<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 20/05/13
 * Time: 23:17
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Gallery\Provider;


use Qwik\Module\Gallery\File;
use Silex\Application;
use Silex\ServiceProviderInterface;

class FileProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['qwik.module.gallery.file'] = $app->share(function ($app) {
            return new File($app['qwik']->getWww(), $app['site']);
        });
    }

    public function boot(Application $app)
    {
    }


}