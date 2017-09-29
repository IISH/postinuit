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
        $this->assertEquals("/postinuit/zoeken.php?search=Firstname", stripDomainnameFromUrl("http://localhost/postinuit/zoeken.php?search=Firstname"));
    }

    public function testGetAndProtectSearchReturnsSafeSearch(){
        $_GET['s'] = "?search=Firstname";
        $this->assertEquals('search=Firstname', getAndProtectSearch());
    }

    public function testGenerateQueryReturnsAProperQuery(){
        $expected = " AND  ( their_name LIKE '%Firstname%'  OR their_organisation LIKE '%Firstname%'  )  AND  ( their_name LIKE '%IISG%'  OR their_organisation LIKE '%IISG%'  ) ";
        $this->assertEquals($expected, Generate_Query(array("their_name", "their_organisation"), explode(' ', "Firstname IISG")));
    }

    public function testGenerateQueryReturnsAFaultyQuery(){
        $expected = " AND  ( their_name LIKE '%Firstname%'  OR their_organisation LIKE '%Firstname%'  )  AND  ( their_name LIKE '%IISG%'  OR their_organisation LIKE '%IISG%'  ) ";
        $this->assertNotEquals($expected, Generate_Query(array("their_name", "their_organisation"), explode(' ', "FirstnameIISG")));
    }

    public function testCreateDateAsStringReturnsAmericanDateAsStringWithoutSeperators(){
        $this->assertEquals('19881202', createDateAsString(1988, 12, 02));
    }

    public function testCreateDateAsStringReturnsNoAmericanDateAsStringWithSeperators(){
        $this->assertNotEquals('1988-12-02', createDateAsString(1988, 12, 02));
    }
}