<?php 
namespace Qwik\Module\Restaurant;

use Qwik\Cms\Module\Module;


/**
 * Gestion d'une carte de restaurant
 */
class Restaurant extends Module{

    /**
     * @return array Envoi des variables pour le template
     */
    public function getTree(){
       return $this->toTree($this->getInfo()->getConfig()->get('config.menu'));
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
                $config = $this->getInfo()->getConfig();
                $priceFormat = $config->get('format.price');
                //On format le prix, si on l'a demandé (config:format) et que le prix est un numérique (is_numeric)
                if(is_numeric($item['price']) && $priceFormat !== null){
                    $item['price'] = sprintf($priceFormat, $item['price']);
                }
            }
    		$return[$where][] = $item;

    	}
    	return $return;
    }
}
