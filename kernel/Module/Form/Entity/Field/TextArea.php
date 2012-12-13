<?php
namespace Qwik\Kernel\Module\Form\Entity\Field;





class TextArea extends Field{
	public function __toString(){
        return '<textarea class="input-xlarge" name="'.$this->getName().'"'.$this->getAttributesAsString().'>'.htmlentities($this->getValue()).'</textarea>';
    }
}