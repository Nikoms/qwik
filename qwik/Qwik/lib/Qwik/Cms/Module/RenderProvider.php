<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 2/05/13
 * Time: 0:57
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Cms\Module;


use Silex\Application;
use Silex\ServiceProviderInterface;

class RenderProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['qwik.module.render'] = $app->share(function ($app) {
            return new Render($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
