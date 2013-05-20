<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 20/05/13
 * Time: 17:33
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Component\Locale;


use Silex\Application;
use Silex\Provider\TranslationServiceProvider;
use Silex\ServiceProviderInterface;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class LocaleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $languages = array('en');
        if(isset($app['site'])){
            $languages = $app['site']->getLanguages();
        }
        $preferredLanguage = $this->getPreferredLanguage('en', $languages);


        $app['qwik.locale'] = $app->share(function ($app) use($languages, $preferredLanguage){
            return new Locale($languages, $preferredLanguage);
        });


        $app['locale'] = $app['qwik.locale']->get();
        $app->register(new TranslationServiceProvider(), array(
            'locale_fallback' => $app['qwik.locale']->getDefault(),
        ));
        $app['translator']->addLoader('yaml', new YamlFileLoader());

    }

    public function boot(Application $app)
    {
    }


    /**
     * Determine which language out of an available set the user prefers most
     * @author http://php.net/manual/en/function.http-negotiate-language.php
     * @return string
     */
    public function getPreferredLanguage($default, array $languages/*$acceptedLanguages = "auto"*/) {
        // if $http_accept_language was left out, read it from the HTTP-Header
        //if ($acceptedLanguages == "auto")
        $acceptedLanguages = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';

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
        $bestlang = $default;
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
            if (in_array($language, $languages) && ($qvalue > $bestqval)) {
                $bestlang = $language;
                $bestqval = $qvalue;
            }
            // if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does)
            else if (in_array($langprefix, $languages) && (($qvalue*0.9) > $bestqval)) {
                $bestlang = $langprefix;
                $bestqval = $qvalue * 0.9;
            }
        }
        return $bestlang;
    }
}
