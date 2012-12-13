<?php
namespace Qwik\Kernel\Module\Form\Entity\Field;

use Qwik\Kernel\App\Module\Module;



abstract class Field{

    private $module;

    private $label;
    private $isRequired;
    private $name;
    private $value;
    
    private $error;

    public function __construct(){

    }
    
    public static function getField($type){
    	switch($type){
    		case 'text':
    			return new Text();
    			break;
    		case 'textarea':
    			return new TextArea();
    			break;
    		case 'email':
    			return new Email();
    			break;
    		case 'date':
    			return new Date();
    			break;
    		default:
    			return new Text();
    			break;
    	}
    }

    public function setModule($module){
        $this->module = $module;
    }
    public function getModule(){
        return $this->module;
    }

    public function setLabel($label){
        $this->label = $label;
    }
    public function getLabel(){
        return $this->label;
    }

    
    public function isRequired(){
        return $this->isRequired;
    }
    public function setIsRequired($required){
         $this->isRequired = $required;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function getName(){
        return $this->name;
    }
    public function setValue($value){
    	//TODO: sanitize value(enlever les caractères invisibles). Peut-être avec filter_var()?
        $this->value = $value;
    }

    public function getValue(){
        return $this->value;
    }
    
    public function setError($error){
    	$this->error = $error;
    }
    public function getError(){
    	return $this->error;
    }

    public function isSpecificValid(){
    	return true;
    }
    
    public function isValid(){
    	//Check la validation spécifique au champs (enfant)
    	if(!$this->isSpecificValid()){
    		return false;
    	}
    	
    	$value = $this->getValue();
    	if($this->isRequired() && (is_null($value) || $value == '')){
            $this->setError(\Qwik\Kernel\App\Language::getValue($this->getModule()->translate('form.mandatory')));
            //$this->setError('Ce champ est obligatoire');
	    	return false;
    	}
    	
    	return true;
    }
    
    public function getAttributesAsString(){
    	return ' '.($this->isRequired()?' required="required"':'').'';
    }
    
    public function getAttributes(){
    	return $this->attributes;
    }
    public function setAttributes($attributes){
    	$this->attributes = $attributes;
    }

    abstract public function __toString();

}