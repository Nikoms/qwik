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
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class Controller implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];


        $controllers->post('/{_locale}/post', function ($_locale) use ($app) {
            //Changement de la langue quand c'est possible...
            $app['translator']->setLocale($app['locale']);

            try {

                $request = Request::createFromGlobals();
                $form = new Form($app['site']->findModule($_POST['_page'], $_POST['_zone'], $_POST['_uniqId']));
                $postedForm = $form->getForm($app['form.factory']);

                //Message par défaut
                $return = array(
                    'valid' => false,
                    'message' => ""
                );


                if ('POST' == $request->getMethod()) {
                    $postedForm->bind($request);

                    if ($postedForm->isValid()) {

                        $mailSender = new MailSender($app['qwik.locale'], $app['translator']);
                        $mailSender->setForceEmailTo(isset($app['config.module']['form']['mail']['redirect']) ? $app['config.module']['form']['mail']['redirect'] : '');
                        //On envoi le mail
                        if ($mailSender->sendForm($form, $postedForm->getData())) {
                            $return['valid'] = true;
                        } else {
                            //Erreur non prévue
                            $return['message'] = $app['translator']->trans('form.unexpectedError');
                        }
                    } else {
                        $return['errors'] = array();
                        foreach ($postedForm->all() as $child) {
                            $errors = $child->getErrors();
                            foreach ($errors as $error) {
                                $return['errors'][$child->getName()] = $error->getMessage();
                            }
                        }
                        $return['message'] = $app['translator']->trans('form.error');
                    }
                }

                //On renvoi le resultat en json
                return json_encode($return);
            } catch (\Exception $ex) {
                //Erreur non prévue
                return json_encode(array('message' => $ex->getMessage(), 'valid' => false));
            }

        })->bind('module_form_send')->assert('_locale', '[a-z]{2}');

        return $controllers;
    }
}