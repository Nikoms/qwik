<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 30/05/13
 * Time: 23:56
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Gmaps;


use Qwik\Cms\Module\IModule;
use Qwik\Cms\Module\Info;
use Qwik\Cms\Module\Instance;
use Silex\Application;

class Module implements IModule{

    public function getInstance(Info $info){
        return new Instance($info);
    }
    /**
     * @param $type
     * @return array
     */
    public function getAssets($type){
        return array();
    }

}