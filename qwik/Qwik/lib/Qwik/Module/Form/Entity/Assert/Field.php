<?php
//namespace Qwik\Module\Form\Entity\Field;
//
//use Qwik\Cms\Module\Module;
//
//
///**
// * Champs d'un formulaire. Cette classe doit être héritée bien sur
// */
//abstract class Field{
//
//    /**
//     * @var Module Module (Form) du field
//     */
//    private $module;
//
//    /**
//     * @var string|array Label du field. Soit un array avec plusieurs langues, soit une string si unilangue
//     */
//    private $label;
//    /**
//     * @var bool Le champ est-il obligatoire?
//     */
//    private $isRequired = false;
//    /**
//     * @var string Nom du field (name="xxx" dans l'input)
//     */
//    private $name;
//    /**
//     * @var string valeur du field (Après le post).
//     */
//    private $value;
//
//    /**
//     * @var string  Erreur du champ
//     */
//    private $error;
//
//    /**
//     * @var array attributs du champs
//     */
//    private $attributes;
//
//    /**
//     *
//     */
//    public function __construct(){
//
//    }
//
//    /**
//     * Factory qui renvoi le bon field en fonction du type
//     * @param $type
//     * @return Date|Email|Text|TextArea
//     */
//    public static function getField($type){
//    	switch($type){
//    		case 'text':
//    			return new Text();
//    			break;
//    		case 'textarea':
//    			return new TextArea();
//    			break;
//    		case 'email':
//    			return new Email();
//    			break;
//    		case 'date':
//    			return new Date();
//    			break;
//    		default:
//    			return new Text();
//    			break;
//    	}
//    }
//
//    /**
//     * @param \Qwik\Cms\Module\Module $module
//     */
//    public function setModule(Module $module){
//        $this->module = $module;
//    }
//
//    /**
//     * @return \Qwik\Cms\Module\Module
//     */
//    public function getModule(){
//        return $this->module;
//    }
//
//    /**
//     * @param mixed $label
//     */
//    public function setLabel($label){
//        $this->label = $label;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getLabel(){
//        return $this->label;
//    }
//
//    /**
//     * @return bool
//     */
//    public function isRequired(){
//        return $this->isRequired;
//    }
//
//    /**
//     * @param bool $required
//     */
//    public function setIsRequired($required){
//         $this->isRequired = (bool) $required;
//    }
//
//    /**
//     * @param string $name
//     */
//    public function setName($name){
//        $this->name = (string) $name;
//    }
//
//    /**
//     * @return string
//     */
//    public function getName(){
//        return $this->name;
//    }
//
//    /**
//     * @param string $value
//     */
//    public function setValue($value){
//        $this->value = (string) $value;
//    }
//
//    /**
//     * @return string
//     */
//    public function getValue(){
//        return $this->value;
//    }
//
//    /**
//     * @param string $error
//     */
//    public function setError($error){
//    	$this->error = (string) $error;
//    }
//
//    /**
//     * @return string
//     */
//    public function getError(){
//    	return $this->error;
//    }
//
//    /**
//     * @return bool A réécrire dans les champs enfants, s'ils ont un test spécifique à faire sur la variable.
//     * Si ce teste passe (par défaut true), alors on applique les tests basiques
//     */
//    protected  function isSpecificValid(){
//    	return true;
//    }
//
//    /**
//     * @return bool Check si le field est valide
//     */
//    public function isValid(){
//    	//Check la validation spécifique au champs (enfant)
//    	if(!$this->isSpecificValid()){
//    		return false;
//    	}
//
//        //La validation de l'enfant est passée, donc on check le "required" qui est le même pour tout le monde
//
//    	$value = $this->getValue();
//        //Si c'est required et vide, on met une erreur (Attention pas faire avec empty, car une string avec "0" renvoi aussi empty, alors que la valeur peut être bonne)
//    	if($this->isRequired() && (is_null($value) || $value == '')){
//            $this->setError('form.field.mandatory');
//            //$this->setError('Ce champ est obligatoire');
//	    	return false;
//    	}
//
//    	return true;
//    }
//
//    /**
//     * @return string Renvoi les attributs du field à mettre dans l'html (pour html5 et classe css entre autre)
//     */
//    public function getAttributesAsString(){
//    	return ' '.($this->isRequired()?' required="required"':'').'';
//    }
//
//    /**
//     * @return array
//     */
//    public function getAttributes(){
//    	return $this->attributes;
//    }
//
//    /**
//     * @param array $attributes
//     */
//    public function setAttributes(array $attributes){
//    	$this->attributes = $attributes;
//    }
//
//    /**
//     * @return string
//     */
//    abstract public function __toString();
//
//}