<?php
namespace Qwik\Kernel\Module\Form\Entity\Field;


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
            //TODO: multilangue
	    	$this->setError('E-mail non valide');
	    	return false;
	    }
	    return true;
	}
	
	public function __construct(){
		parent::__construct();
		$this->setType('email');
	}
	
}