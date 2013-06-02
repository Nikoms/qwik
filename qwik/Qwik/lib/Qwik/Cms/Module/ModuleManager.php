<?php
namespace Qwik\Cms\Module;

use Qwik\Cms\Zone\Zone;
use Qwik\Component\Log\Logger;
use Symfony\Component\Yaml\Yaml;


class ModuleManager
{

    public function __construct()
    {

    }

    /**
     * @param Zone $zone
     * @return Info[]
     */
    public function getByZone(Zone $zone)
    {
        $infos = array();

        foreach ($zone->getConfig() as $key => $config) {
            try {
                $info = new Info();
                //Si c'est pas un array alors, c'est une string qui mène vers le yml de la config
                if (!is_array($config)) {
                    $filePath = $zone->getPage()->getSite()->getPath() . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $config);
                    $config = Yaml::parse($filePath);
                }
                $info->setConfig(new Config($config));
                //$loader = new Loader();
                //$allConfig = $loader->getFileConfig($this->getConfigPath() . 'config.yml');
                $info->setZone($zone);
                //Le nom du module est un cast entre le nom de la zone + _ + la clé du module. Ceci afin que chaque module soit unique
                $info->setUniqId($zone->getName() . '_' . $key);
                $infos[] = $info;
            } catch (\Exception $ex) {
                Logger::getInstance()->error($ex->getMessage(), $ex);
                //Si on a une exception, on va au suivant
                continue;
            }
        }
        return $infos;
    }
}