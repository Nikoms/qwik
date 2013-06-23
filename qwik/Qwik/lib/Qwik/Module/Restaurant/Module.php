<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 30/05/13
 * Time: 23:56
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Restaurant;


use Assetic\Asset\FileAsset;
use Qwik\Cms\Module\Info;
use Silex\Application;

class Module extends \Qwik\Cms\Module\Module
{

    /**
     * @param Info $info
     * @return Restaurant
     */
    public function getInstance(Info $info)
    {
        return new Restaurant($info);
    }

    /**
     * @param $type
     * @return array
     */
    public function getAssets($type)
    {
        $collections = array(
            'javascript' => array(),
            'css' => array(
                new FileAsset('/qwik/module/restaurant/carte.css'),
            )
        );
        return $collections[$type];
    }
}