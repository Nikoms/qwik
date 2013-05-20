<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 13/05/13
 * Time: 23:21
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Form\Entity\Field;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints as Assert;

class Base {

    /**
     * @var array
     */
    private $infos;

    /**
     * @var string
     */
    private $name;

    public function __construct(array $infos){
        $this->setInfos($infos);
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }



    /**
     * @param array $infos
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;
    }

    /**
     * @return string
     */
    public function getType(){
        $infos = $this->getInfos();
        return $infos['type'];
    }
    /**
     * @return bool
     */
    public function isRequired(){
        $infos = $this->getInfos();
        return !empty($infos['config']['required']);
    }

    /**
     * @return array
     */
    public function getConfig(){
        return !empty($infos['config']) ? (array) $infos['config'] : array();
    }

    /**
     * @return string|array
     */
    public function getLabel(){
        $infos = $this->getInfos();
        return $infos['label'];
    }

    /**
     * @return array
     */
    public function getInfos()
    {
        return $this->infos;
    }

    public function getConstraints(){
        $infos = $this->getInfos();
        $return = array();
        if(!empty($infos['config']['required'])){
            $return[] = new Assert\NotBlank();
        }
        return $return;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function valueToString($value){
        return $value;
    }

    /**
     * @param FormBuilder $form
     */
    public function addToForm(FormBuilder $form){

        $config = array(
            'label' => $this->getName(),
            'required'  => $this->isRequired(),
            'constraints' => $this->getConstraints()
        );
        $form->add($this->getName(), $this->getType(), array_merge($config, $this->getConfig()));
    }

}