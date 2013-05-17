<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 2/04/13
 * Time: 21:26
 * To change this template use File | Settings | File Templates.
 */

class LanguageTest extends \Namico\PHPUnit\Framework\TestCase {

    public static function setUpBeforeClass(){
        \Qwik\Component\Locale\Language::init(array('fr','nl','en'));
    }

    /**
     * @Language mock(method1, method2);construct(fr);attr(language=ok;pissette=doudouce;fmip=fmoup)
     * @dataProvider dataProvider(Qwik\Component\Locale\Language)
     */
    public function testInit(\Qwik\Component\Locale\Language $language){
        $this->assertSame('fr', \Qwik\Component\Locale\Language::get());
    }

    public function testGetValueString(){
        $value = "ceci est un test";
        $this->assertSame($value, \Qwik\Component\Locale\Language::getValue($value));
    }

    public function testGetValueArrayOk(){
        $value = array(
            'nl' => "ceci est un test (nl)",
            'fr' => "ceci est un test (fr)",
        );
        $this->assertSame("ceci est un test (fr)", \Qwik\Component\Locale\Language::getValue($value));
    }

    public function testGetValueArrayOnlyTaken(){
        $value = array(
            'nl' => "ceci est un test (nl)",
        );
        $this->assertSame("ceci est un test (nl)", \Qwik\Component\Locale\Language::getValue($value));
    }

    public function testGetValueArrayOnlyGoodTaken(){
        $value = array(
            'fr' => "ceci est un test (fr)",
        );
        $this->assertSame("ceci est un test (fr)", \Qwik\Component\Locale\Language::getValue($value));
    }

    public function testChangeIfPossible(){
        \Qwik\Component\Locale\Language::changeIfPossible('nl');
        $this->assertSame('nl', \Qwik\Component\Locale\Language::get());
    }

    public function testGetValueArrayFirstTaken(){
        $value = array(
            'fr' => "ceci est un test (fr)",
            'en' => "ceci est un test (en)",
        );
        $this->assertSame("ceci est un test (fr)", \Qwik\Component\Locale\Language::getValue($value));
    }
}
