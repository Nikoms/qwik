<?php 
namespace Qwik\Kernel\Module\Restaurant\Entity;

use Qwik\Kernel\App\Module\Module;


/**
 * Gestion d'une carte de restaurant
 */
class Restaurant extends Module{

    /**
     * @return array Envoi des variables pour le template
     */
    public function getTemplateVars(){
        //On donne le contenu de la config
    	$return = parent::getTemplateVars();
        //On rajoute children qui est  la carte en mode "arbre"
        $vars = parent::getTemplateVars();
    	$return['children'] =  $this->toTree($vars['menu']);
        return $return;
    }

    /**
     * Transformation d'un array de la config en un array avec plats + menu
     * @param $children
     * @return array
     */
    private function toTree($children){
    	$return = array(
    		'plats' => array(),
    		'menu' => array(),
    	);
        //On check chaque item
    	foreach($children as $item){

    		//Par défaut l'item va dans "plats"
    		$where = 'plats';

    		//S'il a des enfants, alors, ce n'est plus un plat, mais c'est un menu
    		if(isset($item['children'])){
    			$where = 'menu';
    			$item['children'] = $this->toTree($item['children']);
    		}else{
                $config = $this->getConfig();
                //On format le prix, si on l'a demandé (config:format) et que le prix est un numérique (is_numeric)
                if(is_numeric($item['price']) && isset($config['format']) && isset($config['format']['price'])){
                    $item['price'] = sprintf($config['format']['price'],$item['price']);
                }
            }
    		$return[$where][] = $item;

    	}
    	return $return;
    }
}
