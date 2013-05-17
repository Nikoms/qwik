<?php
namespace Qwik\Module\Form\Entity\Assert;



/**
 * Champ Date
 */
class Date extends Base{


    /**
     * @param \DateTime $value
     * @return string
     */
    public function valueToString($value){
        return $value->format('d/m/Y');
    }
}