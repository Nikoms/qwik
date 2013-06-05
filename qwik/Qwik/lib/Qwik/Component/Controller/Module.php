<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 5/06/13
 * Time: 21:11
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Component\Controller;


use Silex\Application;
use Silex\ControllerProviderInterface;

class Module implements ControllerProviderInterface{

    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        foreach (array_keys($app['qwik.modules']) as $moduleName) {
            $controller = $app['qwik.module']->getController($moduleName);
            $app->mount('/module/' . $moduleName . '/', $controller);
        }

    }
}