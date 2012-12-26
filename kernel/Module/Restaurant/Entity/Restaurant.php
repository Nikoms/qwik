<?php 
namespace Qwik\Kernel\Module\Restaurant\Entity;

use Qwik\Kernel\App\Module\Module;



class Restaurant extends Module{

    public function getTemplateVars(){
    	$return = parent::getTemplateVars();
    	$return['children'] =  $this->toTree(parent::getTemplateVars()['menu']);
        return $return;
    }
    
    private function toTree($children){
    	$return = array(
    		'plats' => array(),
    		'menu' => array(),
    	);
    	foreach($children as $item){

    		//Par défaut ca va dans "plats"
    		$where = 'plats';
    		//S'il a des enfants, alors c'est un menu
    		if(isset($item['children'])){
    			$where = 'menu';
    			$item['children'] = $this->toTree($item['children']);
    		}else{
                $config = $this->getConfig();
                //Pour formater le prix, si on a un format dans la config
                if(is_numeric($item['price']) && isset($config['format']) && isset($config['format']['price'])){
                    $item['price'] = sprintf($config['format']['price'],$item['price']);
                }
            }
    		$return[$where][] = $item;

    	}
    	return $return;
    }
}
