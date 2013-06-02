<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:30
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Cms\Module;


use Qwik\Component\Config\Loader;
use Silex\Application;

class Render
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }


//    /**
//     * @return Config
//     */
//    public function getConfig()
//    {
//        //On va calculer le path de la config du module
//        $pathOfConfig = $this->getDirName() . DIRECTORY_SEPARATOR . 'config';
//        //Transformation de l'array en objet
//        return new Config(Loader::getInstance()->getPathConfig($pathOfConfig));
//    }
//

    /**
     * @param $moduleProvider
     * @param $info
     * @return mixed
     */
    public function render($moduleProvider, $info)
    {
        return $this->app['twig']->render($this->getTemplatePath($moduleProvider), $this->getTemplateVars($moduleProvider->getInstance($info)));
    }


    /**
     * @param $moduleProvider
     * @return string
     */
    public function getTemplatePath($moduleProvider)
    {
        $reflector = new \ReflectionClass(get_class($moduleProvider));
        return basename(dirname($reflector->getFileName())) . '/views/display.html.twig';
    }

    /**
     * @param Instance $instance
     * @return mixed
     */
    protected function getTemplateVars(Instance $instance)
    {
        $return = $instance->getInfo()->getConfig()->get('config');
        $return['this'] = $instance;
        return $return;
    }

}