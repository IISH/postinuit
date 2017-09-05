<?php
/**
 * Created by IntelliJ IDEA.
 * User: Igor van der Bom
 * Date: 1-9-2017
 * Time: 14:48
 */

require_once "./classes/date.inc.php";
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    protected $date;

    public function setUp()
    {
        $this->date = new class_date(2017);
    }

    public function testCreationOfDate(){
        $this->assertNotEmpty($this->date);
    }

    public function testGetDateReturnsDateCorrectly(){
        $this->assertEquals('20170101', $this->date->get());
    }

    public function testGetDateExpectedNotSameAsReturnedDate(){
        $this->assertNotEquals('2017-01-01', $this->date->get());
    }

    public function testGetNumberOfDaysInMonthReturnsThirtyOne(){
        $this->assertEquals(31, $this->date->getNumberOfDaysInMonth());
    }

    public function testGetNumberOfDaysInMonthDoesntReturnThirty(){
        $this->assertNotEquals(30, $this->date->getNumberOfDaysInMonth());
    }

    public function testIsLeapYearReturns2017IsNotALeapYear(){
        $this->assertEquals(0, $this->date->isLeapYear());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsLeapYearReturns2016IsALeapYear(){
        $this->date = new class_date(2016, 5, 13);
        $this->assertEquals(1, $this->date->isLeapYear());
    }

    public function testGetFirstMonthInQuarterReturnsMonthOne(){
        $this->assertEquals(1, $this->date->getFirstMonthInQuarter());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetFirstMonthInQuarterReturnsMonthSeven(){
        $this->date = new class_date(2016, 8, 11);
        $this->assertEquals(7, $this->date->getFirstMonthInQuarter());
    }

    public function testGetLastMonthInQuarterReturnsMonthThree(){
        $this->assertEquals(3, $this->date->getLastMonthInQuarter());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetLastMonthInQuarterReturnsMonthTwelve(){
        $this->date = new class_date(2016, 11, 9);
        $this->assertEquals(12, $this->date->getLastMonthInQuarter());
    }
}