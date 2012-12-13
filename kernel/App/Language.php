<?php

namespace Qwik\Kernel\App;

use Symfony\Component\Yaml\Yaml;

class Language {
	
	private static $current;
	private static $languages;
	
	public static function init(array $languages){
		self::$languages = $languages;
		self::$current = self::getPreferedLanguage();
	}
	
	public static function get(){
		return self::$current;
	}
	
	public static function getDefault(){
		return self::$languages[0];
	}
	
	public static function changeIfPossible($newLanguage){
		if(in_array($newLanguage,self::$languages)){
			self::$current = $newLanguage;
		}
	}
	
	/**
	 * 
	 * Renvoit la valeur selon la langue (il faut que la valeur soit soit string ou array avec fr,nl,en)
	 * @author ndeboose
	 * @version 
	 * @project
	 * @since 8 nov. 2012
	 * @param mixed $value 
	 * @return
	 * @uses @ref 
	 * @keyword 
	 * @db
	 */
	public static function getValue($value){

		//Si c'est pas un array, alors on renvoit directement la valeur car on a pas de choix � faire
		if(!is_array($value)){
			return $value;
		}
		//Si on a une valeur dans la langue du visiteur, cool!
		if(isset($value[self::get()])){
			return $value[self::get()];
		}
		
		//Si on est ici, c'est qu'on a pas trouvé dans la langue du mec... Snif!
		
		//On check si on a une valeur avec la langue principale...
		if(isset($value[self::getDefault()])){
			return $value[self::getDefault()];
		}
		
		//Si on est ici, on a pas trouv� ni avec la langue du user, ni la langue par d�faut...
		
		//On se r�signe � envoyer la premi�re valeur de $value, ce sera donc une langue compl�tement inconnue
		return array_shift($value);
		
	}
	/*
	 determine which language out of an available set the user prefers most
	
	$available_languages        array with language-tag-strings (must be lowercase) that are available
	$http_accept_language    a HTTP_ACCEPT_LANGUAGE string (read from $_SERVER['HTTP_ACCEPT_LANGUAGE'] if left out)
	*/
	public static function getPreferedLanguage(/*$acceptedLanguages = "auto"*/) {
		// if $http_accept_language was left out, read it from the HTTP-Header
		//if ($acceptedLanguages == "auto")
		$acceptedLanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	
		// standard  for HTTP_ACCEPT_LANGUAGE is defined under
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
		// pattern to find is therefore something like this:
		//    1#( language-range [ ";" "q" "=" qvalue ] )
		// where:
		//    language-range  = ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
		//    qvalue         = ( "0" [ "." 0*3DIGIT ] )
		//            | ( "1" [ "." 0*3("0") ] )
		preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" .
				"(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i",
				$acceptedLanguages, $hits, PREG_SET_ORDER);
	
		// default language (in case of no hits) is the first in the array
		$bestlang = self::getDefault();
		$bestqval = 0;
	
		foreach ($hits as $arr) {
			// read data from the array of this hit
			$langprefix = strtolower($arr[1]);
			if (!empty($arr[3])) {
				$langrange = strtolower ($arr[3]);
				$language = $langprefix . "-" . $langrange;
			}else{
				$language = $langprefix;
			}
			$qvalue = 1.0;
			if (!empty($arr[5])){
				$qvalue = floatval($arr[5]);
			}
			 
			// find q-maximal language
			if (in_array($language, self::$languages) && ($qvalue > $bestqval)) {
				$bestlang = $language;
				$bestqval = $qvalue;
			}
			// if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does)
			else if (in_array($langprefix, self::$languages) && (($qvalue*0.9) > $bestqval)) {
				$bestlang = $langprefix;
				$bestqval = $qvalue * 0.9;
			}
		}
		return $bestlang;
	}
}