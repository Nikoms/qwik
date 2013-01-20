<?php

namespace Qwik\Kernel\App\Page;

use Qwik\Kernel\App\Config;


class PageManager {

    /**
     * @var array liste des pages pour chaque path de site
     */
    static private $pages = array();

    /**
     * Récupération d'une page en fonction d'une erreur (ex: 404)
     * @param \Qwik\Kernel\App\Site\Site $site
     * @param \Exception $exception
     * @return null|Page
     */
    public function findErrorBySite(\Qwik\Kernel\App\Site\Site $site, \Exception $exception){
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

        $config = $this->getPagesConfig($site);
        //Si on a pas de config pour cette page, on renvoi null
        if(empty($config[$url])){
            return null;
        }

        return $this->getBuildPage($site, $url, $config[$url]);
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
            $pages[$url] =  $this->getBuildPage($site, $url, $config);
        }
        return $pages;
    }


    /**
     * Renvoi un tableau de config (array) de pages
     * @param \Qwik\Kernel\App\Site\Site $site
     * @return array
     */
    private function getPagesConfig(\Qwik\Kernel\App\Site\Site $site){

        //Check si on a pas déjà le site en cache, car on fait bcp d'appel à cette méthode
        if(empty(self::$pages[$site->getPath()])){
            $pagesPath = $site->getPath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'pages';
            //Check si on a le dossier "pages" avec le nouveau système 1 fichier par page
            if(is_dir($pagesPath)){
                self::$pages[$site->getPath()] = $this->getPagesByPath($pagesPath);
            }else{
                //Pas de pages
                throw new \Exception('No pages config found');
            }


        }

        return self::$pages[$site->getPath()];
    }


    private function getPagesByPath($path){
        //1. Récupération des pages
        $pages = \Qwik\Kernel\App\Config::getInstance()->getPathConfig($path);

        //2. Réorder "naturel" et insensible à la case
        uksort($pages, function ($a, $b){
            return strnatcasecmp($a,$b);
        });

        //3. On supprime les numéros dans les clés
        $return = array();
        foreach($pages as $url => $page){
            //On enlève tous les whitespaces de l'url
            $url = str_replace(' ', '', $url);
            //Le pattern de l'url est par exemple 1-mapage
            $pattern = '/([0-9]+)-(\w+)/i';
            //On remplace le tout par le deuxième match trouvé, c'est à dire le nom de la page
            $replacement = '$2';
            $url = preg_replace($pattern, $replacement, $url);

            $return[$url] = $page;
        }

        return $return;
    }
    /**
     * Renvoi un tableau de config (array) de pages d'erreur
     * @param \Qwik\Kernel\App\Site\Site $site
     * @return array
     */
    public function getPagesErrorConfig(\Qwik\Kernel\App\Site\Site $site){

        $errorsPath = $site->getPath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'errors';
        //Check si on a le dossier "pages" avec le nouveau système 1 fichier par page
        if(is_dir($errorsPath)){
            return $this->getPagesByPath($errorsPath);
        }
        return array();

    }
}