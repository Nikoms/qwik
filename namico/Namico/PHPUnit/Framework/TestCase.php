<?php
namespace Namico\PHPUnit\Framework;

use Namico\PHPUnit\Annotation\Parser;
use Namico\PHPUnit\DataProvider\DataProvider;

class TestCase extends \PHPUnit_Framework_TestCase{

    public function sayHello(){
        exit('hellow');
    }

    private function getDataProviderClassName($method){

        $dataProviderLine = Parser::getAnnotations(get_class($this), $method, 'dataProvider');
        if(empty($dataProviderLine)){
            return;
        }
        $dataProviderLine = $dataProviderLine[0]; //Take the first data provider
        $providerName = trim(str_replace('dataProvider','',$dataProviderLine),'()');

        return $providerName;
    }


    public function dataProvider($method){
        $className = $this->getDataProviderClassName($method);
        if($className === ''){
            return;
        }

        $dataProvider = new DataProvider();

        $dataProvider->setClassName(get_class($this));
        $dataProvider->setMethod($method);
        $dataProvider->setProviderName($className);

        return $dataProvider->getTestCases();
    }
    /**
     *
     */
//    protected function setUp(){
//        $className = get_class($this);
//        $method = $this->getName();
//        $method = strtok($method, " "); //Avoir méthod "testMyMethod with data set #0"
//    }
}