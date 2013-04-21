<?php 
namespace Qwik\Module\Gallery;

use Imagine\Image\ImageInterface;
use Qwik\Cms\Module\Module;
use Qwik\Cms\Page\PageNotFoundException;
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

    /**
     * @return array Renvoi les données pour le template
     */
    public function getTemplateVars(){
		$return = array();
		$return['files'] = array();

        $thumbNailInfo = $this->getConfig()->get('thumbnail', array());

        //On cast en array au cas où path renvoi une string
        $paths = (array) $this->getConfig()->get('path',array());
        //La liste des fichiers
		foreach($paths as $path){
			$return['files'] = array_merge($return['files'], $this->getFiles($path));
		}
        //La position de la galerie
        $return['position'] = $this->getConfig()->get('position', '');
        //TODO : Pas terrible de combiné css/php. Si le css est écrasé, on a perdu. On pourrait peut-être dans la boucle du twig, dire. If i ==perLine, affiche <br />
        //La largeur du module. +5 car on a 5px de padding-left pour chaque img (voir css)
        $perLine = $this->getConfig()->get('perLine');
        $return['width'] = $perLine !== null ? ( $perLine * ($thumbNailInfo['width'] + 5)) . 'px' : '';
        //A-t-on demandé un sous-titre à l'image
        $return['subTitle'] = $this->getConfig()->get('subTitle', false);

		return $return;
	}

    /**
     * @return string Path du thumbnail à générer
     */
    public function getThumbnailPath(){
		$infos = $this->getConfig()->get('thumbnail', array());
		return $this->getZone()->getPage()->getSite()->getVirtualUploadPath() . self::getThumbnailPathFor($infos['width'], $infos['height'], $infos['quality']);
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
    private function getFiles($path){
        $path = (string) $path;
        $fullPath = self::getOriginalWwwPath($this->getZone()->getPage()->getSite(), $path);

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
