<?php

namespace Qwik\Kernel\App\Zone;

use Qwik\Kernel\App\Config;


class ZoneManager {
	
	public function getByPageAndZone($page, $zoneName){
		$configs = $page->getConfig();
		
		//Pas trouver la zone
		if(!isset($configs['zones'][$zoneName])){
			return new Zone();
		}
		
		$zone = new Zone();
		$zone->setConfig($configs['zones'][$zoneName]);
		
		return $zone;
	}


}