<?php

namespace Qwik\Cms\Site;



use Qwik\Application;
use Symfony\Component\HttpFoundation\Request;

class SiteManager {

    /**
     * @param $domain
     * @param $www
     * @return Site
     */
    public function createWithDomain($domain, $www){
		$site = new Site();
		$site->setDomain($domain);

        //TODO: changer ceci, ce n'est pas au site à gérer le www
        $site->setPath($www . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR . $site->getDomain());

		return $site;
	}

    /**
     * @param Request $request
     * @param $www
     * @return Site
     */
    public function getByRequest(Request $request, $www){

        $site = $this->createWithDomain($this->getProperDomain($request->getHttpHost()), $www);
        if($site->exists()){
            return $site;
        }

        //Si le site existe pas, alors on va prendre default pour afficher une page standard (oops, ce site n'existe pas encore)
        return $this->createWithDomain('default', $www);
    }


    /**
     * Récupération du nom de domain cleané (sans local. s'il y en avait un)
     * @param string $domain Nom de domaine (peut avoir un local.) devant
     * @return string
     */
    private function getProperDomain($domain){
        return (strpos($domain, 'local.') === 0) ? substr($domain, 6) : $domain;
    }

}