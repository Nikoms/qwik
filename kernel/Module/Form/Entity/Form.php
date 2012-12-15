<?php 
namespace Qwik\Kernel\Module\Form\Entity;

use Qwik\Kernel\App\Module\Module;
use Qwik\Kernel\App\Language;


class Form extends Module{


    //Attention, si on change aussi, changer la route plus bas
    const PATH = '{_locale}/module/form/post';


    //Pour l'ajout de route pour le post du formulaire
    public static function injectInApp($appManager, $site){

        $appManager->getRouterManager()->post('form', $appManager->getBaseUrl() . Form::PATH,
            function($_locale) use ($site, $appManager) {

                //Changement de la langue quand c'est possible...
                Language::changeIfPossible($_locale);
            	try{
	                $validator = new Validator();
	                $validator->setModule($appManager->findModule($_POST['_page'], $_POST['_zone'], $_POST['_uniqId']));
	                $validator->setPostedDatas($_POST);
	                
	                
	               	$return = array(
	               		'valid' => false,
	               		'message' => "Problème de validation :)"
	               	);
	                if($validator->isValid()){
	                	$return['valid'] = true;
	                	$return['message'] = '';
						if($validator->getModule()->sendMail($validator->getFields())){
		                	$return['valid'] = true;
		                	$return['message'] = '';
						}else{
							$return['message'] = Language::getValue($validator->getModule()->translate('form.unexpectedError'));
						}
	                }else{
	                	$return['errors'] = $validator->getErrors();
                        $return['message'] = Language::getValue($validator->getModule()->translate('form.error'));
	                	//TODO
	                }
	                return json_encode($return);
            	}catch(\Exception $ex){
            		return json_encode(array('message' => $ex->getMessage(), 'valid' => false));
            	}
            }
        );
    }
    public function getPath(){
        return Form::PATH;
    }
    public function getTemplateVars(){
        $return = array();
        $return['action'] =  str_replace('{_locale}', Language::get(), Form::PATH);
        $return['fields'] = $this->getFields();

        return $return;
    }
    
    public function getFields(){
    	$return = array();

        $config = $this->getConfig();
        $fields = (array) $config['fields'];

        foreach($fields as $name => $fieldConfig){
            $field = Field\Field::getField($fieldConfig['type']);
            $field->setModule($this);
            $field->setLabel($fieldConfig['label']);
            $field->setIsRequired(isset($fieldConfig['required']) && ((bool) $fieldConfig['required']));
            $field->setName($name);
            $field->setAttributes(isset($fieldConfig['attributes']) ? $fieldConfig['attributes'] : array());
            $return[$name] = $field;
        }
        return $return;
    }
    
    public function sendMail(array $fields){
    	require_once __DIR__ . '/../../../vendor/Swift/swift_required.php';    	
		\Swift::init(function(){});
		
		$config = $this->getConfig();
		$oldLanguage = Language::get();
		
		//On change si possible avec la langue demandée en config
		if(isset($config['language'])){
			Language::changeIfPossible($config['language']);
		}
		
		$body = Language::getValue($this->translate('form.body'));
		
		//Par défaut, le from est celui à qui on envoit (au cas où)
		$emailFrom = $config['email'];
		foreach($fields as $field){
			//Si j'ai un nom qui s'appelle "email", alors on va dire que c'est le "from"
			if($field->getName() == 'email'){
				$emailFrom = $field->getValue();
			}
			$body.= '- ' . Language::getValue($field->getLabel()).":\n";
			$body.= $field->getValue()."\n\n";
		}
		
		// Create the message
		$message = \Swift_Message::newInstance()
		
		// Give the message a subject
		    ->setSubject(strtoupper($this->getZone()->getPage()->getSite()->getDomain()) . ' - ' . Language::getValue($this->translate('form.subject')))
		
		// Set the From address with an associative array
		    ->setFrom(array($emailFrom))
		
		// Set the To addresses with an associative array
		    ->setTo(array($config['email']))
		
		// Give it a body
		    ->setBody($body)
		
		// And optionally an alternative body
		//    ->addPart('<p>Here is the message itself</p> YOUPIE ca fonctionne :) TOUJOURS :)', 'text/html')
		
		// Optionally add any attachments
		//    ->attach(Swift_Attachment::fromPath('my-document.pdf'))
		;
		// Create the Transport
		//$transport = Swift_SmtpTransport::newInstance('smtp.scarlet.be', 25);
		
		// Mail
		$transport = \Swift_MailTransport::newInstance();
		
		
		// Create the Mailer using your created Transport
		$mailer = \Swift_Mailer::newInstance($transport);
		
		
		$result = $mailer->send($message);
		
		
		Language::changeIfPossible($oldLanguage);
    	
		return $result;
    	
    }
}

