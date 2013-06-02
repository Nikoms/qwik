<?php

namespace Qwik\Cms\Page;

use Qwik\Cms\Zone\Zone;
use Qwik\Cms\Zone\ZoneManager;
use Qwik\Component\Config\Config;
use Qwik\Cms\Site\Site;


/**
 * Classe représentant une page d'un site
 */
class Page
{

    /**
     * Configuration de la page
     * @var array
     */
    private $config;
    /**
     * Site de la page
     * @var Site
     */
    private $site;
    /**
     * Url de la page
     * @var string
     */
    private $url;
    /**
     * Indique si la page est cachée ou non. Attention, cachée ne veut pas dire désactivée. La page sera encore accessible.
     * @var bool
     */
    private $isHidden;
    /**
     * fichiers statiques (css, javascript) nécessaires au bon fonctionnement de la page (récupération dans les modules)
     * @var array
     */
    private $assets;


    /**
     * Tableau des zones de la page
     * @var Zone[]
     */
    private $zones;

    public function __construct()
    {
        $this->config = array();
        $this->assets = array();
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $isHidden bool
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = (bool)$isHidden;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->isHidden;
    }

    /**
     * @param \Qwik\Cms\Site\Site $site
     */
    public function setSite(Site $site)
    {
        $this->site = $site;
    }

    /**
     * @return \Qwik\Cms\Site\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param $url string
     */
    public function setUrl($url)
    {
        $this->url = trim((string)$url);
    }

    /**
     * Retourne le nom du template de la page.
     * @return mixed
     * @throws \Exception Si pas de template
     */
    public function getTemplate()
    {
        return $this->getConfig()->get('template', '');
    }

    /**
     * Renvoi le titre de la page
     * @return string
     */
    public function getTitle()
    {
        return $this->getConfig()->get('title', '');
    }

    /**
     * Retourne la zone en fonction de son nom
     * Cette méthode, en plus de récupérer la zone, la rajoute dans le tableau des zones de la page. Pourquoi? Car on appelle jamais getZones, sauf à la fin, et pendant le chargement de la page, on appelle individuellement chaque zone. Pas besoin de les recalculer donc :)
     * @param string $zoneName
     * @return \Qwik\Cms\Zone\Zone
     * @throws \Exception Si la zone n'a pas été trouvée
     */
    public function getZone($zoneName)
    {

        //Récup des zones
        $zones = $this->getZones();

        //Pas de zone trouvée
        if (!isset($zones[$zoneName])) {
            throw new \Exception('Impossible de trouver la zone "' . $zoneName . '"');
        }

        return $zones[$zoneName];
    }


    /**
     * @return \Qwik\Cms\Zone\Zone[]
     */
    public function getZones()
    {

        if ($this->zones === null) {
            $zoneManager = new ZoneManager();
            $this->zones = $zoneManager->getByPage($this);
        }

        return $this->zones;
    }

}