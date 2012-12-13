<?php
namespace Qwik\Kernel\Module\Form\Entity\Field;





class Email extends Text{

	public function isSpecificValid(){
	
		if(!parent::isSpecificValid()){
			return false;
		}
		
	    if(!filter_var($this->getValue(), FILTER_VALIDATE_EMAIL)){
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