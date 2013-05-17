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
use Qwik\Component\Locale\Language;
use Qwik\Module\Form\Entity\Validator;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class Controller extends \Qwik\Cms\Module\Controller{

    public function __construct(Application $app){
        parent::__construct($app);
        $app['translator']->addResource('yaml', __DIR__.'/locales/fr.yml', 'fr');
        $app['translator']->addResource('yaml', __DIR__.'/locales/en.yml', 'en');
        $app['translator']->addResource('yaml', __DIR__.'/locales/nl.yml', 'nl');


    }

    protected function getModule(Info $info){
        return new Form($info);
    }

    public function injectUrl(){
        $silex = $this->getApplication();

        $silex->post('/{_locale}/module/form/post', function($_locale) use ($silex) {

                //Changement de la langue quand c'est possible...
                $silex['locale'] = Language::changeIfPossible($_locale);
                $silex['translator']->setLocale($silex['locale']);

                try{

                    $request = Request::createFromGlobals();
                    $form = new Form($silex['site']->findModule($_POST['_page'], $_POST['_zone'], $_POST['_uniqId']));
                    $postedForm = $form->getForm($silex);

                    //Message par défaut
                    $return = array(
                        'valid' => false,
                        'message' => ""
                    );


                    if ('POST' == $request->getMethod()) {
                        $postedForm->bind($request);

                        if ($postedForm->isValid()) {
                            $return['valid'] = true;
                            //On envoi le mail
                            if(!$form->sendMail($silex, $postedForm)){
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