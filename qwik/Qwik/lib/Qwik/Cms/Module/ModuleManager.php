<?php
namespace Qwik\Cms\Module;

use Qwik\Cms\Zone\Zone;
use Qwik\Component\Log\Logger;


class ModuleManager{

    public function __construct(){

    }

    /**
     * @param Zone $zone
     * @return Module[]
     */
    public function getByZone(Zone $zone){
        $modules = array();

        foreach($zone->getConfig() as $key => $config){
            try{
                //Le nom du module est un cast entre le nom de la zone + _ + la clé du module. Ceci afin que chaque module soit unique
                $modules[] = Organizer::get($config, $zone, $zone->getName() . '_' . $key);
            }catch(\Exception $ex){
                Logger::getInstance()->error($ex->getMessage(), $ex);
                //Si on a une exception, on va au suivant
                continue;
            }
        }
        return $modules;
    }
}