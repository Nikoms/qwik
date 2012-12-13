<?php

namespace Qwik\Kernel\App\Site;

use Qwik\Kernel\App\Config;


class SiteManager {
	
	public function getByPath($wwwPath, $domain){
		$site = new Site();
		$site->setDomain($domain);
		$site->setWww($wwwPath);
		$site->setPath($site->getWww() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR . $site->getDomain());
		
		return $site;
	}

}