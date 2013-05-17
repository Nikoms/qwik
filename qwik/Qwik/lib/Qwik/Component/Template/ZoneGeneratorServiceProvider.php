<?php

namespace Qwik\Component\Template;


use Silex\Application;
use Silex\ServiceProviderInterface;

class ZoneGeneratorServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['qwik_zone_generator'] = $app->share(function ($app) {
            return new ZoneGenerator($app);
        });
    }

    public function boot(Application $app)
    {
    }
}
