<?php
namespace Qwik\Kernel\Module\Form\Entity\Field;


/**
 * Champ Date
 */
class Date extends Field{


    /**
     * @return string Les attributs pour jQuery-ui, pour gérer les ranges
     */
    public function getAttributesAsString(){
        $attribute = '';
        $attributes = $this->getAttributes();

        //range et link vont de pair!
        if(isset($attributes['range']) && isset($attributes['link'])){
            //Range = begin ou end
            $range = (string) $attributes['range'];
            $attribute .= ' data-range="' . $range . '"';

            //Link = l'autre input lié au range
            $link = (string) $attributes['link'];
            $attribute .= ' data-link="' . $link . '"';
        }

        return $attribute . parent::getAttributesAsString();

    }

    /**
     * @return string
     */
    public function __toString(){
        return '<input class="input-xlarge qwik-form-date" type="text" name="'.$this->getName().'"'.$this->getAttributesAsString().' />';
    }
}