<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 30/05/13
 * Time: 23:56
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\File;


use Qwik\Cms\Module\IModule;
use Silex\Application;
use Qwik\Cms\Module\Info;

class Module implements IModule
{

    /**
     * @param Info $info
     * @return File
     */
    public function getInstance(Info $info)
    {
        return new File($info);
    }

    /**
     * @param $type
     * @return array
     */
    public function getAssets($type)
    {
        return array();
    }

}