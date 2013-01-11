<?php

namespace Qwik\Kernel\App\Page;

use Qwik\Kernel\App\Config;


class PageManager {


    /**
     * Récupération d'une page en fonction d'une erreur (ex: 404)
     * @param \Qwik\Kernel\App\Site\Site $site
     * @param \Exception $exception
     * @param $uri
     * @return null|Page
     */
    public function findErrorBySite(\Qwik\Kernel\App\Site\Site $site, \Exception $exception, $uri){
        $errors = $this->getPagesErrorConfig($site);
        $code = $exception->getCode();
        //Si on trouve pas d'erreur avec ce code, alors on met default
        $code = empty($errors[$code]) ? 'default' : $code;

        //Si on trouve toujours pas le code, on renvoi nll
        if(empty($errors[$code])){
            return null;
        }

        return $this->getBuildPage($site, 'error_' . $code, $errors[$code]);

    }



    /**
     * Récupération d'une page en fonction du site et de l'url
     * @param \Qwik\Kernel\App\Site\Site $site
     * @param $url string
     */
    public function findOneByUrl(\Qwik\Kernel\App\Site\Site $site, $url){
        $url = (string) $url;

        //Si on a pas de config pour cette page, on renvoi null
        if(empty($this->getPagesConfig($site)[$url])){
            return null;
        }

        return $this->getBuildPage($site, $url, $this->getPagesConfig($site)[$url]);
    }

    /**
     * Trouve la première page d'un site
     * @param \Qwik\Kernel\App\Site\Site $site
     * @return Page
     */
    public function findFirst(\Qwik\Kernel\App\Site\Site $site){
        $configs = $this->getPagesConfig($site);
        $firstUrl = key($configs);
        return $this->getBuildPage($site, $firstUrl, $configs[$firstUrl]);
    }

    /**
     * Construit une page sur base des infos donnés
     * @param $name string
     * @param array $config
     * @return Page
     */
    private function getBuildPage(\Qwik\Kernel\App\Site\Site $site, $url, array $config){
        $url = (string) $url;
        $page = new Page();
        $page->setConfig($config);
        $page->setSite($site);
        $page->setUrl($url);
        $page->setIsHidden(!empty($config['hidden']));
        return $page;
    }

    /**
     *
     * @param \Qwik\Kernel\App\Site\Site $site
     * @return \Qwik\Kernel\App\Page\Page[]
     */
    public function findAll(\Qwik\Kernel\App\Site\Site $site){
        $pages = array();
        foreach($this->getPagesConfig($site) as $url => $config){
            $pages[] =  $this->getBuildPage($site, $url, $config);
        }
        return $pages;
    }


    /**
     * Renvoi un tableau de config (array) de pages
     * @param \Qwik\Kernel\App\Site\Site $site
     * @return array
     */
    private function getPagesConfig(\Qwik\Kernel\App\Site\Site $site){
        //TODO: ne pas prendre ca du site
        return $site->getConfig()['pages'];
    }

    /**
     * Renvoi un tableau de config (array) de pages d'erreur
     * @param \Qwik\Kernel\App\Site\Site $site
     * @return array
     */
    public function getPagesErrorConfig(\Qwik\Kernel\App\Site\Site $site){
        //TODO: ne pas prendre ca du site
        $config = $site->getConfig();
        return isset($config['errors']) ? $config['errors'] : array();
    }
}