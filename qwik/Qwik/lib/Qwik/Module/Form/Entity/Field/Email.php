<?php
namespace Qwik\Module\Form\Entity\Field;


use Qwik\Component\Locale\Language;
/**
 * Champ E-mail
 */

class Email extends Text{

    protected function isSpecificValid(){
	
		if(!parent::isSpecificValid()){
			return false;
		}
		//Si c'est pas un mail, c'est pas valide
	    if(!filter_var($this->getValue(), FILTER_VALIDATE_EMAIL)){
	    	$this->setError(Language::getValue($this->getModule()->translate('form.fields.email.notvalid')));
	    	return false;
	    }
	    return true;
	}
	
	public function __construct(){
		parent::__construct();
		$this->setType('email');
	}
	
}