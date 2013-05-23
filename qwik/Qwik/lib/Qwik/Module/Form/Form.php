<?php 
namespace Qwik\Module\Form;

use Qwik\Cms\Module\Module;
use Qwik\Module\Form\Entity\Field\Email;
use Qwik\Module\Form\Entity\Field\Finder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints as Assert;
use Silex\Application;

/**
 * Module "Formulaire"
 */
class Form extends Module{


    /**
     * @param FormFactory $formFactory
     * @return \Symfony\Component\Form\Form
     */
    public function getForm(FormFactory $formFactory){
        $form = $formFactory->createBuilder('form');

        foreach($this->getFields() as $field){
            $field->addToForm($form);
        }

        return $form->getForm();
    }


    /**
     * @return Entity\Field\Base[]
     */
    public function getFields(){
        $fields = array();
        foreach($this->getInfo()->getConfig()->get('config.fields') as $name => $fieldInfos){
            $fieldName = $this->getInfo()->getUniqId().'_'.$name;
            $field = Finder::getField($fieldInfos, $fieldName);
            $fields[$fieldName] = $field;
        }
        return $fields;
    }

}

