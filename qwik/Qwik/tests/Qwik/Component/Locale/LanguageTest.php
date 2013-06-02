<?php
///**
// * Created by JetBrains PhpStorm.
// * User: Nikoms
// * Date: 2/04/13
// * Time: 21:26
// * To change this template use File | Settings | File Templates.
// */
//
//class LanguageTest extends \Namico\PHPUnit\Framework\TestCase {
//
//
//    public function testGetValueString(){
//        $value = "ceci est un test";
//        $this->assertSame($value, \Qwik\Component\Locale\Language::getValue($value));
//    }
//
//    public function testGetValueArrayOk(){
//        $value = array(
//            'nl' => "ceci est un test (nl)",
//            'fr' => "ceci est un test (fr)",
//        );
//        $this->assertSame("ceci est un test (fr)", \Qwik\Component\Locale\Language::getValue($value));
//    }
//
//    public function testGetValueArrayOnlyTaken(){
//        $value = array(
//            'nl' => "ceci est un test (nl)",
//        );
//        $this->assertSame("ceci est un test (nl)", \Qwik\Component\Locale\Language::getValue($value));
//    }
//
//    public function testGetValueArrayOnlyGoodTaken(){
//        $value = array(
//            'fr' => "ceci est un test (fr)",
//        );
//        $this->assertSame("ceci est un test (fr)", \Qwik\Component\Locale\Language::getValue($value));
//    }
//
//
//    public function testGetValueArrayFirstTaken(){
//        $value = array(
//            'fr' => "ceci est un test (fr)",
//            'en' => "ceci est un test (en)",
//        );
//        $this->assertSame("ceci est un test (fr)", \Qwik\Component\Locale\Language::getValue($value));
//    }
//}
