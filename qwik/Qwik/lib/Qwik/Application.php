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
use Qwik\Component\Locale\Language;
use Qwik\Component\Locale\LocaleServiceProvider;
use Qwik\Component\Routing\Service;
use Qwik\Component\Template\ZoneGeneratorServiceProvider;
use Qwik\Environment\Environment;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class Application{

    /**
     * @var \Silex\Application
     */
    private $silex;

    /**
     * @var string
     */
    private $www;

    public function __construct($www, $env, \Silex\Application $silex){
        $this->setSilex($silex);
        $this->setWww($www);
        $silex['qwik'] = $this;
        $silex['debug'] = true;


        //Ajout du site
        $siteManager = new SiteManager();
        $silex['site'] = $siteManager->getByRequest(Request::createFromGlobals(), $this->getWww());

        //Set de l'environnement, après site, c'est mieux :)
        $silex['env'] = new Environment($this, $env);
        $silex['env']->addConvert('site_path', $silex['site']->getPath());
        $silex['env']->addConvert('kernel_path', __DIR__);



        $silex->register(new UrlGeneratorServiceProvider());
        //Modules & zones
        $silex->register(new ModuleServiceProvider());
        $silex->register(new ZoneGeneratorServiceProvider());
        //Traductions
        $silex->register(new LocaleServiceProvider());

        //Registration des providers des modules
        $silex['qwik.module']->registerProviders();

        //Template après tout, car la plupart des providers seront utilisés dans le twigServiceProvider. Il faut donc déjà les loader
        $this->addTemplateManager();

        //Ajout des routes en dernier
        Service::addRoutes($this);

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

    /**
     * @param string $www
     */
    public function setWww($www)
    {
        $this->www = $www;
    }

    /**
     * @return string
     */
    public function getWww()
    {
        return $this->www;
    }






    private function addTemplateManager(){

        //Chemins vers les twig
        $silex = $this->getSilex();
        $paths = $silex['env']->get('template.path');
        foreach($paths as $key => $path){
            $paths[$key] = str_replace('/', DIRECTORY_SEPARATOR, $path);
            //On a peut-être pas le dossier? (genre includes)
            if(!file_exists($path)){
                unset($paths[$key]);
            }
        }

        $this->getSilex()->register(new TwigServiceProvider(), array(
            'twig.path' => $paths,
            'twig.options' => array(
                //Si debug, pas de cache, sinon, ca se trouve dans le path du site
                'cache' => $silex['env']->get('template.cache', false),
                //Mode debug ou pas (voir doc), pour avoir un __toString
                'debug' => $silex['env']->get('template.debug', false),
                //On est strict quand on debug, sinon pas
                'strict_variables' => $silex['env']->get('template.strict', false),
                //On auto escape pas les vars, on le fera quand on en aura besoin
                'autoescape' => false,
            ),
        ));

        //Ajout de la méthode pour traduire un truc dans le template
        $silex['twig']->addFilter('translate', new \Twig_Filter_Function(function($value) use($silex){
            return $silex['qwik.locale']->getValue($value);
        }));
        //Renvoi l'asset
        $silex['twig']->addFunction('asset', new \Twig_Function_Function(function ($uri){
            return Request::createFromGlobals()->getBasePath() . $uri;
        }));

        //Adresse de la page, on rajoute le locale automatiquement
        $silex['twig']->addFunction('pathTo', new \Twig_Function_Function(function ($pageName, $lang = null) use($silex){
            if($lang === null){
                $lang = $silex['locale'];
            }
            return $silex['url_generator']->generate('page', array('_locale' => $lang, 'pageName' => $pageName));
        }));

    }

    /**
     *
     */
    public function run(){
        $this->getSilex()->run();
    }
}