<?php

namespace Qwik\Cms\Site;


//use Symfony\Component\Yaml\Yaml;
use Qwik\Cms\Module\Info;
use Qwik\Cms\Page\Page;


use Qwik\Component\Config\Config;
use Qwik\Component\Config\Loader;

/**
 * Classe qui représente un site
 */
class Site
{

    /**
     * @var Config Tableau de config
     */
    private $config;
    /**
     * @var string domaine représenté
     */
    private $domain;
    /**
     * @var string Chemin vers le dossier non-atteignable du site. Là où l'on va retrouver la config, les templates twig, etc...
     */
    private $path;

    /**
     *
     */
    public function __construct()
    {
    }


    /**
     * @return Config Tableau de la config
     */
    public function getConfig()
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }
        return $this->config;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param $domain
     */
    public function setDomain($domain)
    {
        $this->domain = (string)$domain;
    }


    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = (string)$path;
    }

    /**
     * @return string Langue du site par défaut. Si rien n'est trouvé, on utilise le francais
     */
    public function getDefaultLanguage()
    {
        $languages = $this->getLanguages();
        return (count($languages) > 0) ? $languages[0] : 'fr';
    }

    /**
     * @return array Tableau des langues disponibles sur le site
     */
    public function getLanguages()
    {
        return $this->getConfig()->get('genral.languages.available', array());
//        return isset($config['general']['languages']['available']) ? $config['general']['languages']['available'] : array();
    }

    /**
     * @return string Récupère le titre du site dans la config du site. Vide si aucun titre n'a été trouvé
     */
    public function getTitle()
    {
        return $this->getConfig()->get('general.title','');
    }

    /**
     * @return bool Indique si le site existe. Il faut pour cela que le fichier "general" dans config existe
     */
    public function exists()
    {
        return (bool) $this->getConfig()->get('general', false);
    }

    /**
     * @return bool Indique si le site est un alias d'un autre. Un alias = on reste sur le meme domaine mais on accède aux infos de l'autre
     */
    public function getAlias()
    {
        exit('redirect todo');
    }

    /**
     * @return string Renvoi quel est la redirectiondu site. Renvoi vide si le site n'a pas de redirection
     */
    public function getRedirect()
    {
        exit('redirect todo');
    }

    /**
     * Initialise la config
     * @return Site
     */
    private function initConfig()
    {
        $this->config = new Config(Loader::getInstance()->getPathConfig($this->getPath() . '/structure'));
        return $this;
    }
}