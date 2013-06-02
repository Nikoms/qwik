<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:31
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Gmaps;


use Silex\Application;
use Silex\ControllerProviderInterface;

class Controller implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        return $app['controllers_factory'];
    }
}