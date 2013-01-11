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
     *
     */
    public function __construct(){

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

}