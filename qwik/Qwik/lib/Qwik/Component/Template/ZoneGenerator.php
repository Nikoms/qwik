<?php

namespace Qwik\Component\Template;


use Qwik\Cms\Module\Config;
use Qwik\Cms\Page\Page;
use Silex\Application;

class ZoneGenerator{

    private $zones;
    /**
     * @var \Silex\Application
     */
    protected $app;

    public function __construct(Application $app){
        $this->app = $app;
        $this->zones = array();
    }

    /**
     * @param Page $page
     * @param $zoneName
     * @return string
     */
    public function render(Page $page, $zoneName){
        return $this->app['twig']->render('zone.twig', array('this' => $page->getZone($zoneName)));
    }
}