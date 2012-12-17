<?php 
namespace Qwik\Kernel\Module\Gallery\Entity;

use Qwik\Kernel\App\Module\Module;



class Gallery extends Module{
	
	//Attention, si on change aussi, changer la route plus bas
    //const redirectPrefixThumbnailPath = 'pissette/{domain}/';
    const wwwThumbnailPath = 'cache/gallery/{width}/{height}/{quality}';

	//Récupération de l'url en fonction des infos donnés
	public static function getThumbnailPathFor($domain, $width, $height, $quality){
		return str_replace('{quality}', $quality,
			str_replace('{height}', $height,
				str_replace('{width}', $width,
                    Gallery::wwwThumbnailPath
                )
			)
		);
	}

	//Pour l'ajout de route & autres (extend de twig?) pour les thumbnails
	public static function injectInApp($appManager, $site){
		
		//Ajout d'un helper pour le "titre" d'une image
		\Qwik\Kernel\App\TemplateProxy::getInstance()->getTemplateEngine()->addFilter('toTitle', new \Twig_Filter_Function('Qwik\Kernel\Module\Gallery\Entity\Gallery::toTitle'));

        //return ;
		//Ajout de l'url pour les thumbnails
        //../cache/gallery/120/80/85/images/bureau/Desert.jpg
        $appManager->getRouterManager()->get('gallery', '/' . $site->getVirtualUploadPath() . Gallery::wwwThumbnailPath . '/{url}', function($width, $height, $quality, $url) use ($site) {

            //Pour les espaces, et autres caractères bizarres (ex: photo (2).jpg posait problème car les espaces étaient remplacés par des %20)
            $url = urldecode($url);

        	//Si j'ai pas imagick, alors je prends GD
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
			$thumbPath = $site->getWww() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $site->getRealUploadPath() . '/' . Gallery::getThumbnailPathFor($site->getDomain(), $width, $height, $quality)) . DIRECTORY_SEPARATOR  . $pathOfFile;
			
			//Creation du path du thumbnail
			if(!is_dir($thumbPath)){
				mkdir($thumbPath, 0777, true);
			}
				
			//On va copier l'originale... (si elle existe
            $from = Gallery::getOriginalWwwPath($site, $url);
            if(file_exists($from)){

                //... en thumbnail
                $to = $thumbPath. DIRECTORY_SEPARATOR . $fileName;

                $imagine->open($from)
                ->thumbnail(new \Imagine\Image\Box($width, $height), \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND)
                ->save($to, array('quality' => $quality))
                ;
                return file_get_contents($to);
            }else{
                throw new \Qwik\Kernel\App\Page\PageNotFoundException();
            }
		})->assert('url', '.*');
		
	}
	
	public static function toTitle($fileName){
		$title = ucfirst(pathinfo($fileName, PATHINFO_FILENAME));
		//Remplacement des _ par des espaces
		return str_replace('_', ' ', $title);
	}
	
	//Renvoit les données pour le template
	public function getTemplateVars(){
		$return = array();
		$return['files'] = array();
		
		$config = $this->getConfig();
		$paths = (array) $config['path'];
        $thumbNailInfo = $this->getThumbnailInfos();
		
		foreach($paths as $path){
			$return['files'] = array_merge($return['files'], $this->getFiles($path));
		}
        $return['position'] = isset($config['position']) ? $config['position'] : '';
        //+5 car on a 5px de padding-left pour chaque img (voir css)
        $return['width'] = isset($config['perLine']) ? ( $config['perLine'] * ($thumbNailInfo['width'] + 5)) . 'px' : '';
        $return['subTitle'] = !empty($config['subTitle']);

		return $return;
	}
	
	//Infos du thubmnail à générer
	public function getThumbnailInfos(){
		$config = $this->getConfig();
		return $config['thumbnail'];
	}
	//Path du thumbnail à générer
	public function getThumbnailPath($withPrefix = false){
		$infos = $this->getThumbnailInfos();
		return $this->getZone()->getPage()->getSite()->getVirtualUploadPath() . self::getThumbnailPathFor($this->getZone()->getPage()->getSite()->getDomain(), $infos['width'], $infos['height'], $infos['quality'], $withPrefix);
	}

	public static function getOriginalWwwPath($site, $path){
        $fullPath = $site->getWww() . DIRECTORY_SEPARATOR .  $site->getRealUploadPath() . DIRECTORY_SEPARATOR . $path;
        return str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
    }
	//Récupération des fichiers données
	private function getFiles($path){

        $fullPath = self::getOriginalWwwPath($this->getZone()->getPage()->getSite(), $path);

		if(!is_dir($fullPath)){
			return array();
		}
		
		$return = array();
		foreach (scandir($fullPath) as $file) {
			if (!is_dir($file) && $file != "." && $file != "..") {
				$return[] = $path . '/' .$file;
			}
		}
		return $return;
	}
	
}
