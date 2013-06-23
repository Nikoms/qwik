<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 30/05/13
 * Time: 23:56
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Gallery;


use Assetic\Asset\FileAsset;
use Silex\Application;
use Qwik\Cms\Module\Info;

class Module extends \Qwik\Cms\Module\Module
{

    /**
     * @param Info $info
     * @return Gallery
     */
    public function getInstance(Info $info)
    {
        return new Gallery($info);
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getAssets($type)
    {
        $collections = array(
            'javascript' => array(
                new FileAsset('/qwik/module/gallery/fancybox/jquery.fancybox.pack.js'),
                new FileAsset('/qwik/module/gallery/init.js'),
            ),
            'css' => array(
                new FileAsset('/qwik/module/gallery/fancybox/jquery.fancybox.css'),
                new FileAsset('/qwik/module/gallery/gallery.css'),
            )
        );
        return $collections[$type];
    }


}