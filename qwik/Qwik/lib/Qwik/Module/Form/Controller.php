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

class Controller extends \Qwik\Cms\Module\Controller{

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

    /**
     *
     */
    public function injectUrl(){
        $silex = $this->getApplication();

        $silex->post('/{_locale}/module/form/post', function($_locale) use ($silex) {

                //Changement de la langue quand c'est possible...
                $silex['locale'] = $silex['qwik.locale']->changeIfPossible($_locale);
                $silex['translator']->setLocale($silex['locale']);

                try{

                    $request = Request::createFromGlobals();
                    $form = new Form($silex['site']->findModule($_POST['_page'], $_POST['_zone'], $_POST['_uniqId']));
                    $postedForm = $form->getForm($silex['form.factory']);

                    //Message par défaut
                    $return = array(
                        'valid' => false,
                        'message' => ""
                    );


                    if ('POST' == $request->getMethod()) {
                        $postedForm->bind($request);

                        if ($postedForm->isValid()) {

                            $mailSender = new MailSender($silex['qwik.locale'], $silex['translator'], $silex['env']);
                            //On envoi le mail
                            if($mailSender->sendForm($form, $postedForm->getData())){
                                $return['valid'] = true;
                            }else{
                                //Erreur non prévue
                                $return['message'] = $silex['translator']->trans('form.unexpectedError');
                            }
                        }else{
                            $return['errors'] = array();
                            foreach($postedForm->all() as $child){
                                $errors = $child->getErrors();
                                foreach($errors as $error){
                                    $return['errors'][$child->getName()] = $error->getMessage();
                                }
                            }
                            $return['message'] = $silex['translator']->trans('form.error');
                        }
                    }

                    //On renvoi le resultat en json
                    return json_encode($return);
                }catch(\Exception $ex){
                    //Erreur non prévue
                    return json_encode(array('message' => $ex->getMessage(), 'valid' => false));
                }
            }
        )->bind('module_form_send')->assert('_locale','[a-z]{2}');
    }
}