<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 13/05/13
 * Time: 23:31
 * To change this template use File | Settings | File Templates.
 */

namespace Qwik\Module\Form\Entity\Field;


class Finder {


    /**
     * @param $infos
     * @param $name
     * @return Base|Date|Email|Text|TextArea
     */
    static public function getField($infos, $name){
        $field = new Base($infos);
        switch($infos['type']){
            case 'text':
                $field = new Text($infos);
                break;
            case 'textarea':
                $field = new TextArea($infos);
                break;
            case 'email':
                $field = new Email($infos);
                break;
            case 'date':
                $field = new Date($infos);
                break;
            case 'text':
                $field = new Text($infos);
                break;
        }
        $field->setName($name);
        return $field;
    }
}