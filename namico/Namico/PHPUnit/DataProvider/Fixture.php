<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 22/04/13
 * Time: 22:05
 * To change this template use File | Settings | File Templates.
 */

namespace Namico\PHPUnit\DataProvider;


class Fixture {

    private $params;
    private $className;

    public function __construct($className, $params){
        $this->className = $className;
        $this->params = $params;
    }

    public function getTestCase(){
        $className = $this->getClassName();
        $testCase = new $className();
        return $testCase;
    }

    public function setClassName($className)
    {
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }


}