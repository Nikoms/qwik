<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 30/05/13
 * Time: 23:56
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Form;


use Assetic\Asset\FileAsset;
use Qwik\Cms\Module\IModule;
use Silex\Application;
use Qwik\Cms\Module\Info;

class Module implements IModule
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Renvoi le module et prépare les traductions
     * @param Info $info
     * @return \Qwik\Cms\Module\Module|Form
     */
    public function getInstance(Info $info)
    {
        $form = new Form($info);
        $this->addFieldsTranslations($form);
        return $form;
    }


    /**
     * Ajout des traductions du formulaire
     * @param Form $form
     */
    private function addFieldsTranslations(Form $form)
    {
        $translations = array();
        //Traductions des champs
        foreach ($form->getFields() as $field) {
            $translations[$field->getName()] = $this->app['qwik.locale']->getValue($field->getLabel());
        }
        //Ajout des traductions des champs
        $this->app['translator']->addResource('array', $translations, $this->app['locale']);

    }


    /**
     * @param $type
     * @return array
     */
    public function getAssets($type)
    {
        $collections = array(
            'javascript' => array(
                new FileAsset('/qwik/lib/jquery-ui/js/jquery-ui-1.9.1.custom.min.js'),
                new FileAsset('/qwik/lib/jquery-ui/js/jquery.ui.datepicker-' . $this->app['locale'] . '.js'),
                new FileAsset('/qwik/module/form/init.js'),
            ),
            'css' => array(
                new FileAsset('/qwik/lib/jquery-ui/css/eggplant/jquery-ui-1.9.1.custom.min.css'),
                new FileAsset('/qwik/module/form/form.css'),
            )
        );
        return $collections[$type];
    }
}