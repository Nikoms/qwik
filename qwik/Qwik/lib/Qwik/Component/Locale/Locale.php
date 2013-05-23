<?php

namespace Qwik\Component\Locale;

use Silex\Application;
use Symfony\Component\Yaml\Yaml;

class Locale {
    /**
     * @var string Langue en cours
     */
    private $current;
    /**
     * @var array Liste des langues dispos
     */
    private $languages;

    /**
     * @var \Silex\Application
     */
    private $app;


    /**
     * @param array $languages
     * @param string $current
     */
    public function __construct(array $languages, $current){
        $this->languages = $languages;
		$this->current = $this->changeIfPossible($current);
	}

    /**
     * @return string Langue en cours
     */
    public function get(){
		return $this->current;
	}

    /**
     * @return string Langue par défaut, c'est à dire la première de la liste
     */
    public function getDefault(){
		return $this->languages[0];
	}

    /**
     * @param $newLanguage
     * @return string
     */
    public function changeIfPossible($newLanguage){
        $newLanguage = (string) $newLanguage;
		if(in_array($newLanguage, $this->languages)){
			$this->current = $newLanguage;
		}
        return $this->current;
	}

    /**
     * Renvoi la valeur selon la langue (il faut que la valeur soit soit string ou array avec fr,nl,en,etc...)
     * @param $value
     * @param null $language
     * @return mixed
     */
    public function getValue($value, $language=null){
        if($language === null){
            $language = $this->get();
        }

		//Si c'est pas un array, alors on renvoit directement la valeur car on a pas de choix à faire
		if(!is_array($value)){
			return $value;
		}
		//Si on a une valeur dans la langue du visiteur, cool!
		if(isset($value[$language])){
			return $value[$language];
		}
		
		//Si on est ici, c'est qu'on a pas trouvé dans la langue du visiteur... Snif!
		
		//On check si on a une valeur avec la langue principale...
		if(isset($value[$this->getDefault()])){
			return $value[$this->getDefault()];
		}
		
		//Si on est ici, on a pas trouv& ni avec la langue du visiteur, ni la langue par défaut du site...
		
		//On se résigne à envoyer la première valeur de $value, ce sera donc une langue complêtement inconnue
		return array_shift($value);
		
	}

}