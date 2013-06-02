<?php

namespace Qwik\Cms\Zone;

use Qwik\Cms\Page\Page;


class ZoneManager
{


    /**
     * @param Page $page
     * @return Zone[]
     */
    public function getByPage(Page $page)
    {
        $zones = array();
        foreach ($page->getConfig()->get('zones', array()) as $zoneName => $config) {
            $zones[$zoneName] = $this->getBuildZone($page, $zoneName, $config);
        }

        return $zones;
    }

    /**
     * Renvoi une zone construite en fonction des arguments
     * @param Page $page
     * @param $name string Nom de la zone
     * @param $config array Tableau de config pour la zone
     * @return Zone
     */
    private function getBuildZone(Page $page, $name, array $config)
    {
        $zone = new Zone();
        $zone->setPage($page);
        $zone->setConfig($config);
        $zone->setName($name);
        return $zone;
    }


}