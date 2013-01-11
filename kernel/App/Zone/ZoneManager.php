<?php

namespace Qwik\Kernel\App\Zone;

use Qwik\Kernel\App\Config;
use Qwik\Kernel\App\Page\Page;


class ZoneManager {


    /**
     * @param \Qwik\Kernel\App\Page\Page $page
     * @return Zone[]
     */
    public function getByPage(Page $page){
        $zones =  array();

        foreach($page->getConfig()['zones'] as $zoneName => $config){
            $zones[$zoneName] = $this->getBuildZone($page, $zoneName, $config);
        }

        return $zones;
    }

    /**
     * @param \Qwik\Kernel\App\Page\Page $page
     * @param $zoneName string
     * @return Zone
     */
    /*public function getOneByName(Page $page, $zoneName){


        $zoneName = trim((string) $zoneName);

        if(($zoneName === '') || empty($page->getConfig()['zones'][$zoneName])){
            return null;
        }

        //Renvoi la zone construite
        return $this->getBuildZone($page, $zoneName, $page->getConfig()['zones'][$zoneName]);
	}*/

    /**
     * Renvoi une zone construite en fonction des arguments
     * @param Page $page
     * @param $name string Nom de la zone
     * @param $config array Tableau de config pour la zone
     * @return Zone
     */
    private function getBuildZone(Page $page, $name, array $config){
        $zone = new Zone();
        $zone->setPage($page);
        $zone->setConfig($config);
        $zone->setName($name);
        return $zone;
    }





}