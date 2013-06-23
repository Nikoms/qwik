<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 30/05/13
 * Time: 23:56
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\File;

use Silex\Application;
use Qwik\Cms\Module\Info;

class Module extends \Qwik\Cms\Module\Module
{
    /**
     * @var \Silex\Application
     */
    private $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app){
           $this->app = $app;
    }
    /**
     * @param Info $info
     * @return File
     */
    public function getInstance(Info $info)
    {
        return new File($info, $this->app['qwik.module.file.config']['ressource_path']);
    }
}