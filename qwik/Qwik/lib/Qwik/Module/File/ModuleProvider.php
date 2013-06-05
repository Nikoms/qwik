<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:31
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\File;


use Silex\Application;
use Silex\ServiceProviderInterface;

class ModuleProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['qwik.module.file.config'] = array(
            'ressource_path'   => $app['site']->getPath() . DIRECTORY_SEPARATOR .'resources' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR,
        );


        $app['qwik.module.file'] = $app->share(function ($app) {
            return new Module($app);
        });
    }

    public function boot(Application $app)
    {
    }
}