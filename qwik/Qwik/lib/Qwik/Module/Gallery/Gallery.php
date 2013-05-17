<?php 
namespace Qwik\Module\Gallery;

use Imagine\Image\ImageInterface;
use Qwik\Cms\Module\Module;
use Qwik\Cms\Site\Site;
use Qwik\Component\Template\TemplateProxy;


/**
 * Gestion d'une galerie
 */
class Gallery extends Module{

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
    public static function getThumbnailPathFor($width, $height, $quality){
        $width = (string) $width;
        $height = (string) $height;
        $quality = (string) $quality;
		return str_replace('{quality}', $quality,
			str_replace('{height}', $height,
				str_replace('{width}', $width,
                    Gallery::WWW_THUMBNAIL_PATH
                )
			)
		);
	}

    /**
     * @param string $fileName Nom du fichier
     * @return string Renvoi le nom du fichier sous forme plus "user friendly"
     */
    public static function toTitle($fileName){
		$title = ucfirst(pathinfo($fileName, PATHINFO_FILENAME));
		//Remplacement des _ par des espaces
		return str_replace('_', ' ', $title);
	}

    public function getFiles(){
        $return = array();

        //On cast en array au cas où path renvoi une string
        $paths = (array) $this->getInfo()->getConfig()->get('config.path',array());

        //La liste des fichiers
        foreach($paths as $path){
            $return = array_merge($return, $this->getFilesForPath($path));
        }

        return $return;
    }

    /**
     * @return bool
     */
    public function hasSubtitle(){
        return $this->getInfo()->getConfig()->get('config.subTitle', false);
    }

    public function getVirtualUploadPath(){
        return $this->getInfo()->getZone()->getPage()->getSite()->getVirtualUploadPath();
    }

    /**
     * Largeur total des thumb
     * @return string
     */
    public function getWidth(){
        $thumbNailInfo = $this->getThumbnailInfos();
        //TODO : Pas terrible de combiné css/php. Si le css est écrasé, on a perdu. On pourrait peut-être dans la boucle du twig, dire. If i ==perLine, affiche <br />
        //La largeur du module. +5 car on a 5px de padding-left pour chaque img (voir css)
        $perLine = $this->getInfo()->getConfig()->get('config.perLine');
        return $perLine !== null ? ( $perLine * ($thumbNailInfo['width'] + 5)) . 'px' : '';
    }

    /**
     * La position de la galerie
     * @return string
     */
    public function getPosition(){
        return $this->getInfo()->getConfig()->get('config.position', '');
    }

    public function getThumbnailInfos(){
        return $this->getInfo()->getConfig()->get('config.thumbnail', array());
    }

    /**
     * @return string Path du thumbnail à générer
     */
    public function getThumbnailPath(){
		$infos = $this->getThumbnailInfos();
		return $this->getVirtualUploadPath() . self::getThumbnailPathFor($infos['width'], $infos['height'], $infos['quality']);
	}

    /**
     * @param \Qwik\Cms\Site\Site $site
     * @param $path
     * @return string Chemin vers le fichier original. Vrai chemin, pas celui de de l'url rewriting
     */
    public static function getOriginalWwwPath(Site $site, $path){
        $fullPath = $site->getWww() . DIRECTORY_SEPARATOR .  $site->getRealUploadPath() . $path;
        return str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
    }

    /**
     * Récupération des fichiers données
     * @param string $path
     * @return array Tableau des fichiers dans le dossier
     */
    private function getFilesForPath($path){

        $fullPath = self::getOriginalWwwPath($this->getInfo()->getZone()->getPage()->getSite(), $path);

		if(!is_dir($fullPath)){
			return array();
		}
		
		$return = array();
		foreach (scandir($fullPath) as $file) {
			if (!is_dir($file) && $file != "." && $file != "..") {
                //On met bien un "/" et pas un directory_separator, car c'est destiné pour le html
				$return[] = $path . '/' .$file;
			}
		}
		return $return;
	}
	
}
