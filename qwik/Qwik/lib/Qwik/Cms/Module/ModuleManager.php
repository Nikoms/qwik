<?php
namespace Qwik\Cms\Module;

use Qwik\Cms\Zone\Zone;


class ModuleManager{

    public function __construct(){

    }

    /**
     * @param \Qwik\Cms\Zone\Zone $zone
     * @return Module[]
     */
    public function getByZone(Zone $zone){

        //TODO: (A voir si nécessaire) Dans la config, faire un "modules" pour que zone puisse avec autre choses que des modules, et donc peut-être configurer une zone (ex: la mettre hidden, lui forcer un nom?)
        //Récupération des modules de la config
        $modulesInConfig = $zone->getConfig();

        $modules = array();
        foreach($modulesInConfig as $key => $config){
            try{
                $modules[] = $this->getBuildModule($zone, $key, $config);
            }catch(\Exception $ex){ //Si on a une exception, on va au suivant
                //TODO: Logger erreur
                //echo $ex->getMessage();
                continue;
            }
        }
        return $modules;
    }

    /**
     * Renvoi un Module construit
     * @param \Qwik\Cms\Zone\Zone $zone
     * @param $key string
     * @param $config string|array
     * @return Module
     */
    private function getBuildModule(Zone $zone, $key, $config){
        //Le nom du module est un cast entre le nom de la zone + _ + la clé du module. Ceci afin que chaque module soit unique
        return \Qwik\Cms\Module\Module::get($config, $zone, $zone->getName() . '_' . $key);
    }

}