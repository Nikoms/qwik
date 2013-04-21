<?php


namespace Qwik\Module\Form;

use Qwik\Cms\AppManager;
use Qwik\Cms\Site\Site;
use Qwik\Component\Locale\Language;
use Qwik\Module\Form\Entity\Validator;

class UrlInjector implements \Qwik\Cms\Module\UrlInjector{


    /**
     * @param AppManager $appManager
     * @param Site $site
     * @return mixed|void
     */
    public static function injectInApp(AppManager $appManager, Site $site){


        $appManager->getRouter()->post('module_form_send', '/{_locale}/module/form/post', function($_locale) use ($site, $appManager) {

                //Changement de la langue quand c'est possible...
                Language::changeIfPossible($_locale);

                try{
                    //On va validé le formulaire
                    $validator = new Validator();
                    $validator->setModule($appManager->findModule($_POST['_page'], $_POST['_zone'], $_POST['_uniqId']));
                    $validator->setPostedDatas($_POST);

                    //Message par défaut
                    $return = array(
                        'valid' => false,
                        'message' => ""
                    );


                    if($validator->isValid()){ //Formulaire valide
                        $return['valid'] = true;
                        $return['message'] = '';
                        //On envoi le mail
                        if($validator->getModule()->sendMail($validator->getFields())){
                            $return['valid'] = true;
                            $return['message'] = '';
                        }else{
                            //Erreur non prévue
                            $return['message'] = Language::getValue($validator->getModule()->translate('form.unexpectedError'));
                        }
                    }else{
                        //Erreur gérée
                        $return['errors'] = $validator->getErrors();
                        $return['message'] = Language::getValue($validator->getModule()->translate('form.error'));
                    }
                    //On renvoi le resultat en json
                    return json_encode($return);
                }catch(\Exception $ex){
                    //Erreur non prévue
                    return json_encode(array('message' => $ex->getMessage(), 'valid' => false));
                }
            }
        );
    }


}