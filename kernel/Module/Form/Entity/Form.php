<?php 
namespace Qwik\Kernel\Module\Form\Entity;

use Qwik\Kernel\App\AppManager;
use Qwik\Kernel\App\Module\Module;
use Qwik\Kernel\App\Language;

/**
 * Module "Formulaire"
 */
class Form extends Module{


    /**
     * Pour l'ajout de route pour le post du formulaire
     * @param \Qwik\Kernel\App\AppManager $appManager
     * @param \Qwik\Kernel\App\Site\Site $site
     */
    public static function injectInApp(\Qwik\Kernel\App\AppManager $appManager, \Qwik\Kernel\App\Site\Site $site){


        $appManager->getRouter()->post('module_form_send', '/{_locale}/module/form/post', function($_locale) use ($site, $appManager) {

                //Changement de la langue quand c'est possible...
                Language::changeIfPossible($_locale);

            	try{
                    //TODO: Validator devrait être dans Form
                    //On va validée le formulaire
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

    /**
     * @return array Variables pour le moteur de template
     */
    public function getTemplateVars(){
        $return = array();
        $return['fields'] = $this->getFields();

        return $return;
    }

    /**
     * @return array Liste des objets Field
     */
    public function getFields(){
    	$return = array();

        $config = $this->getConfig();
        $fields = (array) $config['fields'];

        foreach($fields as $name => $fieldConfig){
            $field = Field\Field::getField($fieldConfig['type']);
            $field->setModule($this);
            $field->setLabel($fieldConfig['label']);
            $field->setIsRequired(isset($fieldConfig['required']) && ((bool) $fieldConfig['required']));
            $field->setName($name);
            $field->setAttributes(isset($fieldConfig['attributes']) ? $fieldConfig['attributes'] : array());
            $return[$name] = $field;
        }

        return $return;
    }

    /**
     * Envoi du mail. Méthode qui doit être publique (pour le moment), car appelé de la function anonyme
     * @param array $fields
     * @return int 0|1
     */
    public function sendMail(array $fields){

		$config = $this->getConfig();
		$oldLanguage = Language::get();
		
		//On change si possible avec la langue demandée en config
		if(isset($config['language'])){
			Language::changeIfPossible($config['language']);
		}

        //Début du mail
		$body = Language::getValue($this->translate('form.body'));
		
		//Par défaut, le from est celui à qui on envoi (au cas où on ne trouve pas d'email dans le formulaire)
		$emailFrom = $config['email'];

		foreach($fields as $field){
			//Si j'ai un Field de dont le type est "Email", alors on va dire que c'est le "from" :)
			if($field instanceof \Qwik\Kernel\Module\Form\Entity\Field\Email){
				$emailFrom = $field->getValue();
			}
            //Rajout de l'info dans le body du mail
			$body.= '- ' . Language::getValue($field->getLabel()).":\n";
			$body.= $field->getValue()."\n\n";
		}

        $to = $config['email'];
        if(AppManager::getInstance()->getEnvironment()->get('module.form.mail.redirect')){
            $to = AppManager::getInstance()->getEnvironment()->get('module.form.mail.redirect');
        }

		// Create the message
		$message = \Swift_Message::newInstance()
		
		// Give the message a subject
		    ->setSubject(strtoupper($this->getZone()->getPage()->getSite()->getDomain()) . ' - ' . Language::getValue($this->translate('form.subject')))
		
		// Set the From address with an associative array
		    ->setFrom(array($emailFrom))
		
		// Set the To addresses with an associative array
		    ->setTo(array($to))
		
		// Give it a body
		    ->setBody($body)
		
		// And optionally an alternative body
		//    ->addPart('<p>Here is the message itself</p> YOUPIE ca fonctionne :) TOUJOURS :)', 'text/html')
		
		// Optionally add any attachments
		//    ->attach(Swift_Attachment::fromPath('my-document.pdf'))
		;
		// Create the Transport
		//$transport = Swift_SmtpTransport::newInstance('smtp.scarlet.be', 25);
		
		// Mail
		$transport = \Swift_MailTransport::newInstance();
		
		
		// Create the Mailer using your created Transport
		$mailer = \Swift_Mailer::newInstance($transport);
		
		
		$result = $mailer->send($message);
		
		
		Language::changeIfPossible($oldLanguage);
    	
		return $result;
    	
    }
}

