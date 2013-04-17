<?php
namespace Qwik\Module\Form\Entity\Field;


/**
 * Champ input Text
 */
class Text extends Field{

    /**
     * @var string type (text)
     */
    private $type;

    /**
     *
     */
    public function __construct(){
		parent::__construct();
		$this->setType('text');
	}

    /**
     * @param $type
     */
    public function setType($type){
         $this->type = (string) $type;
    }

    /**
     * @return string
     */
    public function getType(){
        return $this->type;
    }

    /**
     * @return string Attribut sous format html
     */
    public function getAttributesAsString(){
    	$attribute = '';
    	$attributes = $this->getAttributes();
    	if(isset($attributes['max'])){
    		$max = (int) $attributes['max'];
    		$attribute .= ' maxlength="' . $max . '"';
    	}
    	
    	return $attribute . parent::getAttributesAsString();
    	
    }


    /**
     * @return string
     */
    public function __toString(){
        return '<input class="input-xlarge" type="' . $this->getType() . '" name="'.$this->getName().'"'.$this->getAttributesAsString().'/>';
    }
}