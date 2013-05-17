<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 22/04/13
 * Time: 22:05
 * To change this template use File | Settings | File Templates.
 */

namespace Namico\PHPUnit\DataProvider;


use Namico\PHPUnit\Annotation\Parser;

class DataProvider {

    private $className;
    private $providerName;
    private $method;
    /**
     * @var Fixture[]
     */
    private $fixtures;

    public function __construct(){

    }

    private function getShortCut(){
        return substr($this->getProviderName(), strrpos($this->getProviderName(),'\\') + 1);
    }

    public function setClassName($className)
    {
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function setFixtures($fixtures)
    {
        $this->fixtures = $fixtures;
    }

    public function getFixtures()
    {
        if($this->fixtures === null){
            $this->fixtures = array();
            $fixtures = Parser::getAnnotations($this->getClassName(), $this->getMethod(), $this->getShortCut());
            foreach($fixtures as $fixt){
                $fixture = new Fixture($this->getProviderName(), $fixt);
                $this->fixtures[] = $fixture;
            }
        }
        return $this->fixtures;
    }


    public function getTestCases()
    {
        $testCases = array();
        foreach($this->getFixtures() as $fixture){
            $testCases[] = array($fixture->getTestCase());
        }
        return $testCases;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    public function getProviderName()
    {
        return $this->providerName;
    }





}