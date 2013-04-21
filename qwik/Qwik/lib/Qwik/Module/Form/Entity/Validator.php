<?php 

namespace Qwik\Module\Form\Entity;

use Qwik\Module\Form\Form;
use Qwik\Module\Form\Entity\Field\Field;

/**
 * Validateur de formulaire
 */
class Validator{

    /**
     * @var array Champs postés via le formulaire
     */
    private $postedDatas;
    /**
     * @var Form Module "Formulaire" lié
     */
    private $module;
    /**
     * @var array Erreurs du formulaire
     */
    private $errors;

    /**
     *
     */
    public function __construct(){
		$this->errors = array();
	}

    /**
     * @param array $postedDatas
     */
    public function setPostedDatas(array $postedDatas){
		$this->postedDatas = $postedDatas;
	}

    /**
     * @return array
     */
    public function getPostedDatas(){
		return $this->postedDatas;
	}

    /**
     * @param Form $module
     */
    public function setModule(Form $module){
		$this->module = $module;
	}

    /**
     * @return Form
     */
    public function getModule(){
		return $this->module;
	}

    /**
     * Ajout d'une erreur au formulaire
     * @param $key
     * @param $message
     */
    public function addError($key, $message){
		$this->errors[$key] = $message;
	}

    /**
     * @return array
     */
    public function getErrors(){
		return $this->errors;
	}

    /**
     * @return bool Check si le formulaire est valide
     */
    public function isValid(){
		foreach($this->getFields() as $key => $field){
			if(!$field->isValid()){
                //Si le formulaire est pas bon --> on ajoute l'erreur
				$this->addError($key, $field->getError());
			}
		}
        //Si vide, on a pas de problème
		return empty($this->errors);
	}

    /**
     * Renvoi les champs avec leur valeur setté
     * @return Field[]
     */
    public function getFields(){
		$fields = array();
        /**
         * @var $field Field
         */
        foreach($this->getModule()->getFields() as $key => $field){
			$value = isset($this->postedDatas[$key]) ? $this->postedDatas[$key] : ''; 
			$field->setValue($value);
			$fields[$field->getName()] = $field;
		}
		return $fields;
	}
	
}

?>