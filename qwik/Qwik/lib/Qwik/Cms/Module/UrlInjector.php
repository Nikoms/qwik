<?php

namespace Qwik\Cms\Module;

use Qwik\Cms\AppManager;
use Qwik\Cms\Site\Site;

interface UrlInjector{

    /**
     * Ajout d'une route dans l'app
     * @param AppManager $appManager
     * @param Site $site
     * @return mixed
     */
    public static function injectInApp(AppManager $appManager, Site $site);
}