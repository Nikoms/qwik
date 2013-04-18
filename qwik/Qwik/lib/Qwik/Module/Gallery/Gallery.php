<?php 
namespace Qwik\Module\Gallery;

use Qwik\Cms\Module\Module;
use Qwik\Component\Config\Config;
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
     * Pour l'ajout de route & autres (extend de twig) pour les thumbnails
     * @param \Qwik\Cms\AppManager $appManager
     * @param \Qwik\Cms\Site\Site $site
     * @throws \Qwik\Cms\Page\PageNotFoundException
     */
    public static function injectInApp(\Qwik\Cms\AppManager $appManager, \Qwik\Cms\Site\Site $site){
		
		//Ajout d'un helper si on veut affichier le "titre" d'une image (en fonction de son nom)
		TemplateProxy::getInstance()->getTemplateEngine()->addFilter('toTitle', new \Twig_Filter_Function('Qwik\Module\Gallery\Entity\Gallery::toTitle'));


		//Ajout de l'url pour les thumbnails
        //Ex: /q/cache/gallery/120/80/85/images/bureau/Desert.jpg. {url} => images/bureau/Desert.jpg
        $appManager->getRouter()->get('gallery', '/' . $site->getVirtualUploadPath() . Gallery::WWW_THUMBNAIL_PATH . '/{url}', function($width, $height, $quality, $url) use ($appManager, $site) {

            //Pour les espaces, et autres caractères bizarres (ex: photo (2).jpg posait problème car les espaces étaient remplacés par des %20)
            $url = urldecode($url);

        	//Si j'ai pas imagick, alors je prends GD (GD = Problème de mémoire pour les grosses images)
			if (TRUE !== extension_loaded('imagick')){
        		$imagine = new \Imagine\Gd\Imagine();
    		}else{
    			$imagine = new \Imagine\Imagick\Imagine();
    		}

			//Récupération du nom du fichier
			$pathOfFile = pathinfo($url, PATHINFO_DIRNAME);
			
			//Récupération du path
			$fileName = pathinfo($url, PATHINFO_BASENAME);

			//on Calcule le path www du cache (thumbnail)
			$thumbPath = $site->getWww() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $site->getRealUploadPath() . Gallery::getThumbnailPathFor($width, $height, $quality) . '/'  . $pathOfFile);


			//On va copier l'originale... (si elle existe)
            $from = Gallery::getOriginalWwwPath($site, $url);
            if(file_exists($from)){

                //Création de l'objet thumbnail via Imagine (pas encore de création de fichier jpg à cet endroit)
                $thumbnail = $imagine->open($from)->thumbnail(new \Imagine\Image\Box($width, $height), \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND);

                //Si le cache est actif, alors on sauve, au bon endroit, en fichier physique
                //TODO: pour le moment, comme "asset" renvoi l'adresse directe, on passe théoriquement toujours ici :)
                if($appManager->getEnvironment()->get('module.gallery.cache')){

                    //Creation du path du thumbnail
                    if(!is_dir($thumbPath)){
                        mkdir($thumbPath, 0777, true);
                    }

                    //On sauve où les thumbnail ?!
                    $to = $thumbPath. DIRECTORY_SEPARATOR . $fileName;

                    //sauvegarde en fichier
                    $thumbnail->save($to, array('quality' => $quality));
                }


                //On renvoi gentillement l'image pour l'afficher
                $response = new \Qwik\Component\Routing\Response();
                $response->setContent($thumbnail->get(pathinfo($fileName, \PATHINFO_EXTENSION)));
                $response->setFileName($fileName);
                return $response;

            }else{
                throw new \Qwik\Cms\Page\PageNotFoundException();
            }
		})->assert('url', '.*');
		
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
		
		$config = $this->getConfig();
		$paths = (array) $config['path'];
        $thumbNailInfo = $this->getThumbnailInfos();

        //La liste des fichiers
		foreach($paths as $path){
			$return['files'] = array_merge($return['files'], $this->getFiles($path));
		}
        //La position de la galerie
        $return['position'] = isset($config['position']) ? $config['position'] : '';
        //TODO : Pas terrible de combiné css/php. Si le css est écrasé, on a perdu. On pourrait peut-être dans la boucle du twig, dire. If i ==perLine, affiche <br />
        //La largeur du module. +5 car on a 5px de padding-left pour chaque img (voir css)
        $return['width'] = isset($config['perLine']) ? ( $config['perLine'] * ($thumbNailInfo['width'] + 5)) . 'px' : '';
        //A-t-on demandé un sous-titre à l'image
        $return['subTitle'] = !empty($config['subTitle']);

		return $return;
	}

    /**
     * @return array Infos du thubmnail à générer (taille, qualité, etc...)
     */
    public function getThumbnailInfos(){
		$config = $this->getConfig();
		return $config['thumbnail'];
	}

    /**
     * @return string Path du thumbnail à générer
     */
    public function getThumbnailPath(){
		$infos = $this->getThumbnailInfos();
		return $this->getZone()->getPage()->getSite()->getVirtualUploadPath() . self::getThumbnailPathFor($infos['width'], $infos['height'], $infos['quality']);
	}

    /**
     * @param \Qwik\Cms\Site\Site $site
     * @param $path
     * @return string Chemin vers le fichier original. Vrai chemin, pas celui de de l'url rewriting
     */
    public static function getOriginalWwwPath(\Qwik\Cms\Site\Site $site, $path){
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