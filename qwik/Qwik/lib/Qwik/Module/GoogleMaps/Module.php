<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 30/05/13
 * Time: 23:56
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\GoogleMaps;


use Assetic\Asset\FileAsset;
use Silex\Application;

class Module extends \Qwik\Cms\Module\Module
{

    /**
     * @param $type
     * @return array
     */
    public function getAssets($type)
    {
        $collections = array(
            'javascript' => array(
                new FileAsset('/qwik/module/google/maps/gmaps.js'),
            ),
            'css' => array(
                new FileAsset('/qwik/module/google/maps/gmaps.css'),
            )
        );
        return $collections[$type];
    }
}