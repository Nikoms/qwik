<?php

namespace Qwik\Cms\Module;

use Qwik\Component\Config\Config;
use Qwik\Component\Config\Loader;
use Qwik\Cms\Zone\Zone;

/**
 * Class Module
 * @package Qwik\Cms\Module
 */
class Info
{

    /**
     * Zone dans laquelle appartient le module
     * @var \Qwik\Cms\Zone\Zone
     */
    private $zone;
    /**
     * La config du module indiquée dans le yml de la page
     * @var Config
     */
    private $config;

    /**
     * Id unique du module
     * @var string
     */
    private $uniqId;


    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }


    /**
     * @param Zone $zone
     */
    public function setZone(Zone $zone)
    {
        $this->zone = $zone;
    }

    /**
     * @return Zone
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Nom de la classe en cours, sans namespace
     * @return string
     */
    public function getName()
    {
        return $this->getConfig()->get('module');
    }

    /**
     * @param $uniqId string
     */
    public function setUniqId($uniqId)
    {
        $this->uniqId = trim((string)$uniqId);
    }

    /**
     * @return string
     */
    public function getUniqId()
    {
        return $this->uniqId;
    }
}