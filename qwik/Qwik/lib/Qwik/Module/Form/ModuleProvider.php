<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 6/05/13
 * Time: 20:31
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Form;


use Qwik\Cms\Module\Info;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
//TODO : implements ServiceProvider

class ModuleProvider extends \Qwik\Cms\Module\Controller{

    public function __construct(Application $app){
        parent::__construct($app);
        $app['translator']->addResource('yaml', __DIR__.'/translation/fr.yml', 'fr');
        $app['translator']->addResource('yaml', __DIR__.'/translation/en.yml', 'en');
        $app['translator']->addResource('yaml', __DIR__.'/translation/nl.yml', 'nl');
    }

    /**
     * Renvoi le module et prépare les traductions
     * @param Info $info
     * @return \Qwik\Cms\Module\Module|Form
     */
    protected function getModule(Info $info){
        $form = new Form($info);

        $this->addFieldsTranslations($form);
        return $form;
    }

    /**
     * Ajout des traductions du formulaire
     * @param Form $form
     */
    private function addFieldsTranslations(Form $form){
        $app = $this->getApplication();
        $translations = array();
        //Traductions des champs
        foreach($form->getFields() as $field){
            $translations[$field->getName()] = $app['qwik.locale']->getValue($field->getLabel());
        }

        //Ajout des traductions des champs
        $app['translator']->addResource('array',$translations, $app['qwik.locale']->get());

    }
}