<?php

namespace Qwik\Cms\Page;


use Qwik\Cms\Site\Site;
use Qwik\Component\Config\Config;
use Qwik\Component\Config\Loader;

class PageManager
{

    /**
     * @var Page[] liste des pages pour chaque path de site
     */
    static private $pages = array();

    /**
     * Récupération d'une page en fonction d'une erreur (ex: 404)
     * @param Site $site
     * @param \Exception $exception
     * @return null|Page
     */
    public function findErrorBySite(Site $site, \Exception $exception)
    {
        $errors = $this->getPagesErrorConfig($site);
        $code = $exception->getCode();
        //Si on trouve pas d'erreur avec ce code, alors on met default
        $code = empty($errors[$code]) ? 'default' : $code;

        //Si on trouve toujours pas le code, on renvoi nll
        if (empty($errors[$code])) {
            return null;
        }
        return $this->getBuildPage($site, 'error_' . $code, $errors[$code]);
    }



    /**
     * Renvoi un tableau de config (array) de pages d'erreur
     * @param \Qwik\Cms\Site\Site $site
     * @return array
     */
    public function getPagesErrorConfig(\Qwik\Cms\Site\Site $site)
    {
        //TODO qwik.path['site']['errors']
        $errorsPath = $site->getPath() . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'errors';
        //Check si on a le dossier "pages" avec le nouveau système 1 fichier par page
        if (is_dir($errorsPath)) {
            return $this->getPagesByPath($errorsPath);
        }
        return array();

    }
}