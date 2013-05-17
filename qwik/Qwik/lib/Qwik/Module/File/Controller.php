<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:31
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\File;


use Qwik\Cms\Module\Info;

class Controller extends \Qwik\Cms\Module\Controller{
    /**
     * @param Info $info
     * @return Gallery
     */
    protected function getModule(Info $info){
        return new File($info);
    }

}