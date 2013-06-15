<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 1/05/13
 * Time: 17:21
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik;


use Qwik\Cms\Module\ModuleServiceProvider;
use Qwik\Cms\Page\PageServiceProvider;
use Qwik\Cms\Site\SiteManager;
use Qwik\Component\Controller\Admin;
use Qwik\Component\Controller\Module;
use Qwik\Component\Controller\Page;
use Qwik\Component\Locale\LocaleServiceProvider;
use Qwik\Component\Template\ZoneGeneratorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Igorw\Silex\ConfigServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class Application
{

    /**
     * @var \Silex\Application
     */
    private $silex;

    public function __construct(\Silex\Application $silex)
    {
        $this->setSilex($silex);
    }

    /**
     * Initialisation
     */
    public function init()
    {
        $silex = $this->getSilex();
        $this->loadQwikConfig($silex);
        //Ajout du site
        $siteManager = new SiteManager();

        $domain = $this->getProperDomain(Request::createFromGlobals()->getHost());
        $path = $silex['qwik.path']['sites'] . $domain;
        $silex['site'] = $siteManager->createWithDomain($domain, $path);

        if(!$silex['site']->exists()){
            $silex['site'] = $siteManager->createWithDomain('default', $path);
        }

        $this->registerLessProvider($silex);

        //Registration des providers des modules
        $silex['qwik.module']->registerProviders();

        //Template après tout, car la plupart des providers seront utilisés dans le twigServiceProvider. Il faut donc déjà les loader
        $this->addTemplateManager();


        //Mount des pages
        $silex->mount('/', new Page());
        //Mount de l'admin
        $silex->mount('/admin/', new Admin());
        //Mount du module
        $module = new Module();
        $module->connect($silex);

        //TODO 404 and co à prendre... Voir PageManager pour le moment, et déplacer les logiques dans PageService
        $silex->error(function (\Exception $e, $code) {
            switch ($code) {
                case 404:
                    $message = 'The requested page could not be found.';
                    break;
                default:
                    $message = 'We are sorry, but something went terribly wrong.';
            }

            return $message;
        });

    }

    /**
     *
     */
    private function loadQwikConfig(\Silex\Application $app){

        $replacement = array(
            'kernel_path' => __DIR__,
            'www_path' => $app['qwik.www'],
        );

        $dir = __DIR__ . '/Resources/config/';
        $app->register(new ConfigServiceProvider($dir . 'qwik.yml', $replacement));
    }

    /**
     * @param \Silex\Application $app
     */
    private function registerLessProvider(\Silex\Application $app)
    {

        $replacement = array(
            'site_path' => $app['site']->getPath(),
            'site_domain' => $app['site']->getDomain(),
            'kernel_path' => __DIR__,
        );


        $app->register(new ConfigServiceProvider($app['qwik.path']['default_config'] . 'default.yml', $replacement));
        $app->register(new ConfigServiceProvider($app['qwik.path']['default_config'] . 'default_' . $app['qwik.config'] . '.yml', $replacement));

        if(file_exists($app['qwik.path']['site']['config'] . 'default.yml')){
            $app->register(new ConfigServiceProvider($app['qwik.path']['site']['config'] . 'default.yml', $replacement));
        }
        if(file_exists($app['qwik.path']['site']['config'] . 'default_' . $app['qwik.config'] . '.yml')){
            $app->register(new ConfigServiceProvider($app['qwik.path']['site']['config'] . 'default_' . $app['qwik.config'] . '.yml', $replacement));
        }

        $app->register(new UrlGeneratorServiceProvider());
        //Modules & zones
        $app->register(new ModuleServiceProvider());
        $app->register(new ZoneGeneratorServiceProvider());
        $app->register(new PageServiceProvider());
        //Traductions
        $app->register(new LocaleServiceProvider());
    }

    /**
     * @param \Silex\Application $silex
     */
    public function setSilex($silex)
    {
        $this->silex = $silex;
    }

    /**
     * @return \Silex\Application
     */
    public function getSilex()
    {
        return $this->silex;
    }

    private function addTemplateManager()
    {

        //Chemins vers les twig
        $silex = $this->getSilex();

        $this->getSilex()->register(new TwigServiceProvider(), array(
            'twig.path' => $silex['twig.path'], //Obligé de laisser ceci, car le Provider set les valeurs à vide
            'twig.options' => $silex['twig.options'], //Obligé de laisser ceci, car le Provider set les valeurs à vide
        ));

        //Ajout de la méthode pour traduire un truc dans le template
        $silex['twig']->addFilter('translate', new \Twig_Filter_Function(function ($value) use ($silex) {
            return $silex['qwik.locale']->getValue($value);
        }));
        //Renvoi l'asset
        $silex['twig']->addFunction('asset', new \Twig_Function_Function(function ($uri) {
            return Request::createFromGlobals()->getBasePath() . $uri;
        }));

        //Adresse de la page, on rajoute le locale automatiquement
        $silex['twig']->addFunction('pathTo', new \Twig_Function_Function(function ($pageName, $lang = null) use ($silex) {
            if ($lang === null) {
                $lang = $silex['locale'];
            }
            return $silex['url_generator']->generate('page', array('_locale' => $lang, 'pageName' => $pageName));
        }));

    }


    /**
     * Récupération du nom de domain cleané (sans local. s'il y en avait un)
     * @param string $host Nom de domaine (peut avoir un local.) devant
     * @return string
     */
    private function getProperDomain($host)
    {
        return (strpos($host, 'local.') === 0) ? substr($host, 6) : $host;
    }

}