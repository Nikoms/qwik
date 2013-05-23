<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 23/05/13
 * Time: 22:50
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Form;


use Qwik\Component\Locale\Locale;
use Qwik\Environment\Environment;
use Qwik\Module\Form\Entity\Field\Email;
use Symfony\Component\Translation\Translator;

class MailSender {

    /**
     * @var \Qwik\Component\Locale\Locale
     */
    private $locale;

    /**
     * @var
     */
    private $translator;

    /**
     * @var \Qwik\Environment\Environment
     */
    private $env;

    public function __construct(Locale $locale, Translator $translator, Environment $env){
        $this->locale = $locale;
        $this->translator = $translator;
        $this->env = $env;
    }

    public function sendForm(Form $form, $datas){

        $mailLocale = $form->getInfo()->getConfig()->get('config.language', $this->locale->get());

        //Début du mail
        $body = $this->translator->trans('form.body', array(), 'messages', $mailLocale);

        //Par défaut, le from est celui à qui on envoi (au cas où on ne trouve pas d'email dans le formulaire)
        $replyTo = $emailFrom = $form->getInfo()->getConfig()->get('config.email');

        $fields = $form->getFields();
        foreach($datas as $name => $value){
            if(!isset($fields[$name])){
                continue;
            }
            $field = $fields[$name];

            //Si j'ai un Field de dont le type est "Email", alors on va dire que c'est le "reply to" :)
            if($field instanceof Email){
                $replyTo = $value;
            }
            $body.= '- ' . $this->locale->getValue($field->getLabel(), $mailLocale) . ":\n" . $field->valueToString($value) . "\n\n";
        }


        $to = $this->env->get('module.form.mail.redirect', $form->getInfo()->getConfig()->get('config.email'));

        // Create the message
        $message = \Swift_Message::newInstance()
            ->setSubject(strtoupper($form->getInfo()->getZone()->getPage()->getSite()->getDomain()) . ' - ' . $this->translator->trans('form.subject', array(), 'messages', $mailLocale))
            ->setFrom(array($emailFrom))
            ->setReplyTo($replyTo)
            ->setTo(array($to))
            ->setBody($body);

        $mailer = \Swift_Mailer::newInstance(\Swift_MailTransport::newInstance());
        $result = $mailer->send($message);

        return $result;

    }
}