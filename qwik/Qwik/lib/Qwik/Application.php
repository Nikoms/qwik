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
use Qwik\Cms\Site\SiteManager;
use Qwik\Component\Controller\Admin;
use Qwik\Component\Controller\Page;
use Qwik\Component\Locale\LocaleServiceProvider;
use Qwik\Component\Template\ZoneGeneratorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Igorw\Silex\ConfigServiceProvider;

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
        //Ajout du site
        $siteManager = new SiteManager();
        $silex['site'] = $siteManager->getByRequest(Request::createFromGlobals()->getHost(), $silex['qwik.www']);

        $this->registerLessProvider($silex);

        //Registration des providers des modules
        $silex['qwik.module']->registerProviders();

        //Template après tout, car la plupart des providers seront utilisés dans le twigServiceProvider. Il faut donc déjà les loader
        $this->addTemplateManager();


        //Mount des pages
        $silex->mount('/', new Page());
        //Mount de l'admin
        $silex->mount('/admin/', new Admin());

    }

    /**
     * @param \Silex\Application $app
     */
    private function registerLessProvider(\Silex\Application $app)
    {


        $replacement = array(
            'site_path' => $app['site']->getPath(),
            'kernel_path' => __DIR__,
        );

        $dir = __DIR__ . '/Resources/config/';

        $app->register(new ConfigServiceProvider($dir . 'default.yml', $replacement));
        $app->register(new ConfigServiceProvider($dir . 'default_' . $app['qwik.config'] . '.yml', $replacement));


        $app->register(new UrlGeneratorServiceProvider());
        //Modules & zones
        $app->register(new ModuleServiceProvider());
        $app->register(new ZoneGeneratorServiceProvider());
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
     *
     */
    public function run()
    {
        $this->getSilex()->run();
    }
}