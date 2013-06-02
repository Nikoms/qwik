<?php

namespace Qwik\Cms\Module;
/**
 * Class Bundle
 * @package Qwik\Cms\Module
 */
class Instance
{


    /**
     * @var Info
     */
    private $info;

    public function __construct(Info $info)
    {
        $this->setInfo($info);
    }

    /**
     * @param \Qwik\Cms\Module\Info $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @return \Qwik\Cms\Module\Info
     */
    public function getInfo()
    {
        return $this->info;
    }


}