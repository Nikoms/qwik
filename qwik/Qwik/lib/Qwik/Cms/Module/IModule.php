<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 31/05/13
 * Time: 1:24
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Cms\Module;


use Qwik\Cms\Page\Page;

interface IModule
{
    public function getInstance(Info $info);

    public function getAssets($type);
}