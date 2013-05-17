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

class ModuleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['qwik_module'] = $app->share(function ($app) {
            return new ModuleService($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
