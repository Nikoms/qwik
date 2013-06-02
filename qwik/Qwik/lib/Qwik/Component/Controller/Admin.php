<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 2/06/13
 * Time: 23:51
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Component\Controller;


use Silex\Application;
use Silex\ControllerProviderInterface;

class Admin implements ControllerProviderInterface
{

    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        //Clear varnish et les templates
        $controllers->get('/cc', array($this, 'clearCache'));

        return $controllers;
    }

    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function clearCache(Application $app)
    {
        //Clear Vanish, que si on a curl init
        if (function_exists('curl_init')) {
            //Clear varnish
            header("Cache-Control: max-age=1"); // don't cache ourself
            //error_reporting(E_ALL);
            //ini_set("display_errors", 1);
            // Set to true to hide varnish result
            define("SILENT", false);
            if ($ch = curl_init("http://" . $app['site']->getDomain() . "/")) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PURGE");
                curl_setopt($ch, CURLOPT_NOBODY, SILENT);
                curl_exec($ch);
                curl_close($ch);
            }
        }

        //Clear du template
        $app['twig']->clearCacheFiles();

        return Page::getFirstPageRedirect($app);
    }
}