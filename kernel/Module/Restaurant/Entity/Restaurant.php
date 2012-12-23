<?php 
namespace Qwik\Kernel\Module\Restaurant\Entity;

use Qwik\Kernel\App\Module\Module;



class Restaurant extends Module{

    public function getTemplateVars(){
    	$return = parent::getTemplateVars();
    	$return['children'] =  Restaurant::toTree(parent::getTemplateVars()['menu']);
        return $return;
    }
    
    static private function toTree($children){
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
    			$item['children'] = self::toTree($item['children']);
    		}
    		$return[$where][] = $item;
    	}
    	return $return;
    }
}
