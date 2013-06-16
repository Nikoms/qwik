<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 15/06/13
 * Time: 20:57
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Cms\Page;


use Qwik\Cms\Site\Site;
use Qwik\Component\Config\Loader;
use Qwik\Component\Config\Config;
use Silex\Application;

class PageService {

    /**
     * @var \Qwik\Cms\Site\Site
     */
    private $site;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $errorPath;

    /**
     * @var Page[] liste des pages pour chaque path de site
     */
    private $pages;

    /**
     * @param Site $site
     * @param string $path
     * @param string $errorPath
     */
    public function __construct(Site $site, $path, $errorPath)
    {
        $this->site = $site;
        $this->path = $path; //$app['qwik.path']['site']['pages'];
        $this->errorPath = $errorPath; //$app['qwik.path']['site']['errors'];
        $this->pages =  array();
    }

    /**
     * @return array|Page[]
     */
    public function getAllPages()
    {
        if(empty($this->pages)){
            foreach ($this->getPagesConfig() as $url => $config) {
                $this->pages[$url] = $this->getBuildPage($url, $config);
            }
        }
        return $this->pages;
    }
    /**
     * Construit une page sur base des infos donnés
     * @param string $url
     * @param array $config
     * @return Page
     */
    private function getBuildPage($url, array $config)
    {
        $url = (string)$url;
        $page = new Page();
        $page->setConfig(new Config($config));
        $page->setSite($this->site);
        $page->setUrl($url);
        $page->setIsHidden(!empty($config['hidden']));
        return $page;
    }

    /**
     * @return array
     */
    private function getPagesConfig()
    {
        return $this->getPagesInPath(str_replace('/', DIRECTORY_SEPARATOR, $this->path));
    }

    /**
     * @param $path
     * @return array
     */
    private function getPagesInPath($path)
    {
        if(!is_dir($path)){
            return array();
        }
        //1. Récupération des pages
        $pages = Loader::getInstance()->getPathConfig($path);

        //2. Réorder "naturel" et insensible à la case
        uksort($pages, function ($a, $b) {
            return strnatcasecmp($a, $b);
        });

        //3. On supprime les numéros dans les clés
        $return = array();
        foreach ($pages as $url => $page) {
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
     * Récupération d'une page en fonction du site et de l'url
     * @param $url string
     * @return null|Page
     */
    public function getOneByUrl($url)
    {
        $url = (string) $url;

        $config = $this->getPagesConfig();
        //Si on a pas de config pour cette page, on renvoi null
        if (empty($config[$url])) {
            return null;
        }

        //Récupération des pages, et on renvoi celui qu'on a
        $pages = $this->getAllPages($this->site);
        return isset($pages[$url]) ? $pages[$url] : null;
    }




    /**
     * Trouve la première page d'un site
     * @param Site $site
     * @return Page
     */
    public function getFirst(Site $site)
    {
        $pages = $this->getAllPages($site);
        return current($pages);
    }


    /**
     * Récupération d'une page en fonction d'une erreur (ex: 404)
     * @param \Exception $exception
     * @param $code
     * @return null|Page
     */
    public function getErrorPage(\Exception $exception, $code)
    {
        $errors = $this->getPagesErrorConfig();
        //Si on trouve pas d'erreur avec ce code, alors on met default
        $code = empty($errors[$code]) ? 'default' : $code;

        //Si on trouve toujours pas le code, on renvoi nll
        if (empty($errors[$code])) {
            return null;
        }
        return $this->getBuildPage('error_' . $code, $errors[$code]);
    }


    /**
     * Renvoi un tableau de config (array) des pages d'erreur
     * @return array
     */
    private function getPagesErrorConfig()
    {
        return $this->getPagesInPath(str_replace('/', DIRECTORY_SEPARATOR, $this->errorPath));
    }

}