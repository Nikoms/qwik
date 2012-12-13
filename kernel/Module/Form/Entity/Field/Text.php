<?php
namespace Qwik\Kernel\Module\Form\Entity\Field;





class Text extends Field{
	
	private $type;
	
	public function __construct(){
		parent::__construct();
		$this->setType('text');
	}

    public function setType($type){
         $this->type = $type;
    }
    public function getType(){
        return $this->type;
    }
	
    public function getAttributesAsString(){
    	$attribute = '';
    	$attributes = $this->getAttributes();
    	if(isset($attributes['max'])){
    		$max = (int) $attributes['max'];
    		$attribute .= ' maxlength="' . $max . '"';
    	}
    	
    	return $attribute . parent::getAttributesAsString();
    	
    }
    
    

    
	public function __toString(){
        return '<input class="input-xlarge" type="' . $this->getType() . '" name="'.$this->getName().'"'.$this->getAttributesAsString().'/>';
    }
}