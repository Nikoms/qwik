<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 31/05/13
 * Time: 1:24
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Cms\Module;

class Module
{
    /**
     * @param Info $info
     * @return Instance
     */
    public function getInstance(Info $info)
    {
        return new Instance($info);
    }

    /**
     * Liste des assets en fonction du type
     * @param $type
     * @return array
     */
    public function getAssets($type)
    {
        return array();
    }
}