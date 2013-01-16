<?php


namespace Qwik\Kernel\App\Routing;

/**
 * Classe qui gère un retour vers le navigateur, c'est à dire, le plus souvent un html :)
 */
class Response {

    /**
     * @var string contenu html de la page
     */
    private $content;
    /**
     * @var array Tableau contenu des header à envoyer
     */
    private $headers;

    /**
     * @var string Nom du fichier
     */
    private $fileName = '';

    /**
     *
     */
    public function __construct(){
        $this->headers = array();
    }

    /**
     * @param $content string
     */
    public function setContent($content){
        $this->content = (string) $content;
    }

    /**
     * @return string
     */
    public function getContent(){
        return $this->content;
    }

    /**
     * Set d'un header
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value){
        $this->headers[(string) $key] = (string) $value;
    }

    /**
     * @return array
     */
    private function getHeaders(){
        return $this->headers;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = (string) $fileName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }


    private function initContentType(){
        if($this->getFileName() !== ''){
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // Retourne le type mime à la extension mimetype
            $header = finfo_file($finfo, $this->getFileName());
            finfo_close($finfo);
            //Maybe une exception...
            $ext = pathinfo($this->getFileName(), PATHINFO_EXTENSION);
            //Si l'extention est js ou css, il y a une exception pour que les sites s'affichent correctement
            if($ext === 'js'){
                $header = 'application/javascript';
            }elseif($ext === 'css'){
                $header = 'text/css';
            }
            $this->setHeader('Content-type', $header);
        }
    }

    /**
     * Affichage du response
     */
    public function render(){
        $this->setHeader('Content-Length', strlen($this->getContent()));
        $this->initContentType();
        foreach($this->headers as $key => $value){
            header($key . ':' . $value);
        }
        echo $this->getContent();
    }
}