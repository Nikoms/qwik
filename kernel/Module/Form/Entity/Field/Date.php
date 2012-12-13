<?php
namespace Qwik\Kernel\Module\Form\Entity\Field;





class Date extends Field{


    public function getAttributesAsString(){
        $attribute = '';
        $attributes = $this->getAttributes();

        //range et link vont de pair!
        if(isset($attributes['range']) && isset($attributes['link'])){
            $range = (string) $attributes['range'];
            $attribute .= ' data-range="' . $range . '"';

            $link = (string) $attributes['link'];
            $attribute .= ' data-link="' . $link . '"';
        }

        return $attribute . parent::getAttributesAsString();

    }
	
	public function __toString(){
        return '<input class="input-xlarge qwik-form-date" type="text" name="'.$this->getName().'"'.$this->getAttributesAsString().' />';
    }
}