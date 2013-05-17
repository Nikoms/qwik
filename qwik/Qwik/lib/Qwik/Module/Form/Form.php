<?php 
namespace Qwik\Module\Form;

use Qwik\Cms\AppManager;
use Qwik\Cms\Module\Module;
use Qwik\Component\Locale\Language;
use Qwik\Module\Form\Entity\Assert\Email;
use Qwik\Module\Form\Entity\Assert\Finder;
use Symfony\Component\Validator\Constraints as Assert;
use Silex\Application;

/**
 * Module "Formulaire"
 */
class Form extends Module{


    /**
     * @param Application $app
     * @return mixed
     */
    public function getForm(Application $app){
        return $this->getFormBuilder($app)->getForm();
    }

    /**
     * @param Application $app
     * @return mixed
     */
    private function getFormBuilder(Application $app){
        $form = $app['form.factory']->createBuilder('form');

        foreach($this->getFields() as $field){
            $config = array(
                'label' => Language::getValue($field->getLabel()),
                'required'  => $field->isRequired(),
                'constraints' => $field->getConstraints()
            );
            $form->add($field->getName(), $field->getType(), array_merge($config, $field->getConfig()));
        }

        return $form;
    }

    /**
     * @return \Qwik\Module\Form\Entity\Assert[]
     */
    private function getFields(){
        $fields = array();
        foreach($this->getInfo()->getConfig()->get('config.fields') as $name => $fieldInfos){
            $field = Finder::getField($fieldInfos, $name);
            $fields[$name] = $field;
        }
        return $fields;
    }


    /**
     * Envoi du mail. Méthode qui doit être publique (pour le moment), car appelé de la function anonyme
     * @param Application $app
     * @param \Symfony\Component\Form\Form $form
     * @return int
     */
    public function sendMail(Application $app, \Symfony\Component\Form\Form $form){

        //TODO: faire quelque chose pour changer tout automatiquement la langue (silex[locale] etc...)
		$oldLanguage = Language::get();
		
		//On change si possible avec la langue demandée en config
		if($this->getInfo()->getConfig()->get('config.language', false)){
			Language::changeIfPossible($this->getInfo()->getConfig()->get('config.language'));
		}


        //TODO :attention, pour le moment, la traduction est celle du visiteur. Il faut donc que switcher de langue pour la mettre à celle voulue
        //Début du mail
		$body = $app['translator']->trans('form.body');
		
		//Par défaut, le from est celui à qui on envoi (au cas où on ne trouve pas d'email dans le formulaire)
		$replyTo = $emailFrom = $this->getInfo()->getConfig()->get('config.email');

        $fields = $this->getFields();
        foreach($form->getData() as $name => $value){
            if(!isset($fields[$name])){
                continue;
            }
            $field = $fields[$name];

            //Si j'ai un Field de dont le type est "Email", alors on va dire que c'est le "from" :)
            if($field instanceof Email){
                $replyTo = $value;
            }
            $body.= '- ' . Language::getValue($field->getLabel()) . ":\n" . $field->valueToString($value) . "\n\n";
        }


        $to = $this->getInfo()->getConfig()->get('config.email');
        if($app['env']->get('module.form.mail.redirect')){
            $to = $app['env']->get('module.form.mail.redirect');
        }


		// Create the message
		$message = \Swift_Message::newInstance()
            //TODO :attention, pour le moment, la traduction est celle du visiteur. Il faut donc que switcher de langue pour la mettre à celle voulue
		    ->setSubject(strtoupper($this->getInfo()->getZone()->getPage()->getSite()->getDomain()) . ' - ' . $app['translator']->trans('form.subject'))
		    ->setFrom(array($emailFrom))
            ->setReplyTo($replyTo)
		    ->setTo(array($to))
		    ->setBody($body);

		$mailer = \Swift_Mailer::newInstance(\Swift_MailTransport::newInstance());
		$result = $mailer->send($message);
		
		
		Language::changeIfPossible($oldLanguage);
    	
		return $result;
    	
    }

}

