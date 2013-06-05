<?php

namespace Qwik\Cms\Site;


use Qwik\Application;
use Symfony\Component\HttpFoundation\Request;

class SiteManager
{

    /**
     * @param $domain
     * @param $www
     * @return Site
     */
    public function createWithDomain($domain, $path)
    {
        $site = new Site();
        $site->setDomain($domain);
        $site->setPath($path);
        return $site;
    }


}