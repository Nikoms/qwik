<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 22/04/13
 * Time: 21:22
 * To change this template use File | Settings | File Templates.
 */

namespace Namico\PHPUnit\Annotation;


class Parser {

    /**
     * @param $className
     * @param $method
     * @param $annotation
     * @return array
     * @author http://blog.behance.net/dev/custom-phpunit-annotations
     */
    public static function getAnnotations($className, $method, $annotation){
        $reflection = new \ReflectionMethod($className, $method);
        $tag = '@' . $annotation;
        $docComment = $reflection->getDocComment();


        if ( empty( $docComment ) ){
            return array();
        }

        $regex = "/{$tag} (.*)(\\r\\n|\\r|\\n)/U";
        preg_match_all( $regex, $docComment, $matches );

        if ( empty( $matches[1] ) ){
            return array();
        }

        // Removed extra index
        $matches = $matches[1];

        array_map(function($match){
            return trim($match);
        }, $matches);


        return $matches;
    }
}