<?php
namespace Qwik\Module\Gallery;

use Qwik\Cms\Module\Instance;


/**
 * Gestion d'une galerie
 */
class Gallery extends Instance
{

    /**
     * Constante qui situe les images miniatures
     */
    const WWW_THUMBNAIL_PATH = 'cache/gallery/{width}/{height}/{quality}';

    /**
     * Récupération de l'url du miniature en fonction des infos donnés
     * @param string $width
     * @param string $height
     * @param string $quality
     * @return string
     */
    public static function getThumbnailPathFor($width, $height, $quality)
    {
        return str_replace('{quality}', $quality,
            str_replace('{height}', $height,
                str_replace('{width}', $width,
                    Gallery::WWW_THUMBNAIL_PATH
                )
            )
        );
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        //On cast en array au cas où path renvoi une string
        return (array)$this->getInfo()->getConfig()->get('config.path', array());

    }

    /**
     * @return bool
     */
    public function hasSubtitle()
    {
        return $this->getInfo()->getConfig()->get('config.subTitle', false);
    }

    /**
     * @return string
     */
    public function getVirtualUploadPath()
    {
        return $this->getInfo()->getZone()->getPage()->getSite()->getVirtualUploadPath();
    }

    /**
     * @return string Path du thumbnail à générer
     */
    public function getThumbnailPath()
    {
        $infos = $this->getInfo()->getConfig()->get('config.thumbnail', array());
        return
            $this->getVirtualUploadPath()
            . self::getThumbnailPathFor($infos['width'], $infos['height'], $infos['quality']);
    }

}
