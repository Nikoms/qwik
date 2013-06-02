<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 20/05/13
 * Time: 17:33
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Component\Locale;


use Silex\Application;
use Silex\Provider\TranslationServiceProvider;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class LocaleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $languages = array('en');
        if (isset($app['site'])) {
            $languages = $app['site']->getLanguages();
        }


        $preferredLanguage = Request::createFromGlobals()->getPreferredLanguage($languages);

        $app['qwik.locale'] = $app->share(function ($app) use ($languages) {
            return new Locale($languages, $app);
        });


        $app->register(new TranslationServiceProvider(), array(
            'locale_fallback' => $preferredLanguage,
        ));

        $app['translator']->addLoader('yaml', new YamlFileLoader());

    }

    public function boot(Application $app)
    {
    }

}
