<?php

namespace Qwik\Cms\Zone;

use Qwik\Cms\Module\Info;
use Qwik\Cms\Page\Page;

/**
 * Une zone dans une page qui contient des modules
 */
class Zone
{

    /**
     * @var array Tableau de la config de la zone
     */
    private $config;
    /**
     * @var Page Page à laquelle appartient la zone
     */
    private $page;
    /**
     * @var Info[] Tableau de module se retrouvant dans la zone
     */
    private $modules;

    /**
     * @var string Nom de la zone
     */
    private $name;

    /**
     *
     */
    public function __construct()
    {
        $this->config = array();
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \Qwik\Cms\Page\Page $page
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
    }

    /**
     * @return \Qwik\Cms\Page\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param $name string
     */
    public function setName($name)
    {
        $this->name = (string)$name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}