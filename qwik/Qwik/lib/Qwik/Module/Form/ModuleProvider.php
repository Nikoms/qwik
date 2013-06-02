<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:31
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Form;


use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ModuleProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {

        //TODO: mettre ca ailleurs? Parce que ca marche pas dans le share on le boot
        $app->register(new FormServiceProvider());
        $app->register(new ValidatorServiceProvider());

        //Si je mets ca dans le share, je n'ai plus les traductions (tester: cliquer sur envoyer directement et on voit "form.error")
        $app['translator']->addResource('yaml', __DIR__ . '/translation/fr.yml', 'fr');
        $app['translator']->addResource('yaml', __DIR__ . '/translation/en.yml', 'en');
        $app['translator']->addResource('yaml', __DIR__ . '/translation/nl.yml', 'nl');

        $app['qwik.module.form'] = $app->share(function ($app) {
            return new Module($app);
        });
    }

    public function boot(Application $app)
    {
    }

}