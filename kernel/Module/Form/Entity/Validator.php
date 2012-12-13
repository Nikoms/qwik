<?php 

namespace Qwik\Kernel\Module\Form\Entity;

class Validator{
	
	private $postedData;	
	private $module;
	private $errors;
	
	public function __construct(){
		$this->errors = array();
	}
	
	public function setPostedDatas($postedDatas){
		$this->postedDatas = $postedDatas;
	}
	public function getPostedDatas(){
		return $this->postedDatas;
	}
	
	public function setModule(Form $module){
		$this->module = $module;
	}
	public function getModule(){
		return $this->module;
	}
	
	public function addError($key, $message){
		$this->errors[$key] = $message;
	}
	public function getErrors(){
		return $this->errors;
	}
	
	public function isValid(){
		foreach($this->getFields() as $key => $field){
			if(!$field->isValid()){
				$this->addError($key, $field->getError());
			}
		}
		return empty($this->errors);
	}
	
	public function getFields(){
		$fields = array();
		foreach($this->getModule()->getFields() as $key => $field){
			$value = isset($this->postedDatas[$key]) ? $this->postedDatas[$key] : ''; 
			$field->setValue($value);
			$fields[$field->getName()] = $field;
		}
		return $fields;
	}
	
}

?>