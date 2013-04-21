<?php 
namespace Qwik\Module\Form;

use Qwik\Cms\AppManager;
use Qwik\Cms\Module\Module;
use Qwik\Component\Locale\Language;
use Qwik\Module\Form\Entity\Field\Email;
use Qwik\Module\Form\Entity\Field\Field;

/**
 * Module "Formulaire"
 */
class Form extends Module{


    /**
     * @return array Variables pour le moteur de template
     */
    public function getTemplateVars(){
        $return = array();
        $return['fields'] = $this->getFields();

        return $return;
    }

    /**
     * @return Field Liste des objets Field
     */
    public function getFields(){
    	$return = array();

        foreach($this->getConfig()->get('fields') as $name => $fieldConfig){
            $field = Entity\Field\Field::getField($fieldConfig['type']);
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

		//$config = $this->getConfig();
		$oldLanguage = Language::get();
		
		//On change si possible avec la langue demandée en config
		if($this->getConfig()->get('language', false)){
			Language::changeIfPossible($this->getConfig()->get('language'));
		}

        //Début du mail
		$body = Language::getValue($this->translate('form.body'));
		
		//Par défaut, le from est celui à qui on envoi (au cas où on ne trouve pas d'email dans le formulaire)
		$emailFrom = $this->getConfig()->get('email');

		foreach($fields as $field){
			//Si j'ai un Field de dont le type est "Email", alors on va dire que c'est le "from" :)
			if($field instanceof Email){
				$emailFrom = $field->getValue();
			}
            //Rajout de l'info dans le body du mail
			$body.= '- ' . Language::getValue($field->getLabel()).":\n";
			$body.= $field->getValue()."\n\n";
		}

        $to = $this->getConfig()->get('email');
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
		    ->setBody($body);

		// Mail
		$transport = \Swift_MailTransport::newInstance();
		
		
		// Create the Mailer using your created Transport
		$mailer = \Swift_Mailer::newInstance($transport);
		
		
		$result = $mailer->send($message);
		
		
		Language::changeIfPossible($oldLanguage);
    	
		return $result;
    	
    }
}

