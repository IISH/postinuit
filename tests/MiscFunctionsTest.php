<?php
/**
 * Created by IntelliJ IDEA.
 * User: Igor van der Bom
 * Date: 31-8-2017
 * Time: 15:08
 */

require_once "./classes/_misc_functions.inc.php";
require_once "./classes/website_protection.inc.php";
use PHPUnit\Framework\TestCase;

class MiscFunctionsTest extends TestCase
{
    protected $protect;

    public function setUp(){
        $this->protect = new WebsiteProtection();
    }

    public function testHashEqualsReturnsTrueWhenSameValue(){
        $this->assertEquals(true, hash_equals("Test", "Test"));
    }

    public function testHashEqualsReturnsFalseWhenOneValueIsAllCapitals(){
        $this->assertEquals(false, hash_equals("TEST", "Test"));
    }

    public function testReplaceDoubleTripleSpacesIsSuccesfull(){
        $this->assertEquals("Text with one space in between", replaceDoubleTripleSpaces("Text with   one space   in  between"));
    }

    public function testReplaceDoubleTripleSpacesReplacesMoreThanThreeSpaces(){
        $this->assertEquals("Text with one space in between", replaceDoubleTripleSpaces("Text       with     one  space    in  between"));
    }

    public function testValueOrReturnsTheValueGiven(){
        $this->assertEquals("Value given", valueOr("Value given"));
    }

    public function testValueOrReturnsQuestionMark(){
        $this->assertEquals("?", valueOr(""));
    }

    public function testCreateUrlReturnsAProperUrlSuccesfully(){
        $parts = ['url'=> "url",'label'=> "label"];
        $this->assertEquals("<a href=\"url\">label</a>", createUrl($parts));
    }

    public function testCreateUrlReturnsAFalseUrl(){
        $parts = ['url'=> "label",'label'=> "url"];
        $this->assertNotEquals("<a href=\"url\">label</a>", createUrl($parts));
    }

    public function testSplitStringIntoArraySplitsMultipleLettersWithSpaces(){
        $testArray = ["a","a","a","a"];
        $this->assertEquals($testArray, splitStringIntoArray("a a a a"));
    }

    public function testSplitStringIntoArrayDoesntSplitASingleWordIntoAnArray(){
        $testArray = ["a","a","a","a"];
        $this->assertNotEquals($testArray, splitStringIntoArray("aaaa"));
    }

    public function testStripDomainnameFromUrlReturnsTheDomainName(){
        $this->assertEquals("/postinuit/zoeken.php?search=Gordan", stripDomainnameFromUrl("http://localhost/postinuit/zoeken.php?search=Gordan"));
    }

//    /**
//     * @runInSeparateProcess
//     */
//    public function testGoBackMakesThePageGoBackToMainPage(){
//        $_SERVER['HTTP_REFERER'] = 'http://localhost/postinuit/zoeken.php';
//        goBack();
//        header('index.php');
////        fwrite(STDERR, print_r($_SERVER['HTTP_REFERER'], TRUE));
//        print(PHP_EOL);
//        print_r(get_headers('http://localhost/postinuit/zoeken.php'));
//        print_r($_SERVER['HTTP_REFERER']);
//        $this->assertEquals('http://localhost/postinuit/zoeken.php', $_SERVER['HTTP_REFERER']);
//    }

    public function testGetAndProtectSearchReturnsSafeSearch(){
        $_GET['s'] = "?search=Gordan";
        $this->assertEquals('search=Gordan', getAndProtectSearch());
    }

    public function testGenerateQueryReturnsAProperQuery(){
        $expected = " AND  ( their_name LIKE '%Gordan%'  OR their_organisation LIKE '%Gordan%'  )  AND  ( their_name LIKE '%IISG%'  OR their_organisation LIKE '%IISG%'  ) ";
        $this->assertEquals($expected, Generate_Query(array("their_name", "their_organisation"), explode(' ', "Gordan IISG")));
    }

    public function testGenerateQueryReturnsAFaultyQuery(){
        $expected = " AND  ( their_name LIKE '%Gordan%'  OR their_organisation LIKE '%Gordan%'  )  AND  ( their_name LIKE '%IISG%'  OR their_organisation LIKE '%IISG%'  ) ";
        $this->assertNotEquals($expected, Generate_Query(array("their_name", "their_organisation"), explode(' ', "GordanIISG")));
    }

    public function testCreateDateAsStringReturnsAmericanDateAsStringWithoutSeperators(){
        $this->assertEquals('19881202', createDateAsString(1988, 12, 02));
    }

    public function testCreateDateAsStringReturnsNoAmericanDateAsStringWithSeperators(){
        $this->assertNotEquals('1988-12-02', createDateAsString(1988, 12, 02));
    }

//    /**
//     * @runInSeparateProcess
//     */
//    public function testGetBackUrlReturnsAProperUrl(){
//        session_start();
//        $_GET['burl'] = '/postinuit/geavanceerd_zoeken.php';
//
////        // Create a stub for the SomeClass class.
////        $stub = $this->createMock(WebsiteProtection::class);
////
////        // Configure the stub.
////        $stub->method('get_left_part')
////            ->willReturn('localhost/postinuit');
//
//        $this->assertEquals('bleh', getBackUrl());
//    }

}