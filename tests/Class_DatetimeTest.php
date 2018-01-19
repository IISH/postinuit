<?php
/**
 * User: Igor van der Bom
 * Date: 1-9-2017
 * Time: 16:19
 */

require_once "./classes/datetime.inc.php";
require_once "./classes/date.inc.php";
require_once "./classes/website_protection.inc.php";
use PHPUnit\Framework\TestCase;

class Class_DatetimeTest extends TestCase {
    protected $datetime;

    public function setUp(){
        $this->datetime = new class_datetime();
    }

    public function testGetQueryDateReturnsCurrentDate(){
        $currentDate = date('Ymd');
        $this->assertEquals($currentDate, $this->datetime->getQueryDate());
    }

    public function testGetQueryDateReturnsTheGivenDate(){
        $_GET['d'] = '1980-12-05';
        $this->assertEquals('1980-12-05', $this->datetime->getQueryDate());
    }

    public function testConvertTimeInMinutesToTimeInHoursAndMinutesReturnsSixtyHours(){
        $this->assertEquals('60:00', $this->datetime->ConvertTimeInMinutesToTimeInHoursAndMinutes(3600));
    }

    public function testConvertTimeInMinutesToTimeInHoursAndMinutesReturnsThirteenHoursAndTwentyFiveMinutes(){
        $this->assertEquals('13:25', $this->datetime->ConvertTimeInMinutesToTimeInHoursAndMinutes(805));
    }

    public function testConvertTimeInMinutesToTimeInHoursAndMinutesReturnsZero(){
        $this->assertEquals('0:00', $this->datetime->ConvertTimeInMinutesToTimeInHoursAndMinutes(''));
    }

    public function testCheckDateReturnsCorrectDateGiven(){
        $dateToCheck['y'] = 2012; $dateToCheck['m'] = 05; $dateToCheck['d'] = 07;
        $dateExpected['y'] = 2012; $dateExpected['m'] = 05; $dateExpected['d'] = 07;
        $this->assertEquals($dateExpected, $this->datetime->check_date($dateToCheck));
    }

    public function testCheckDateReturnsImprovedDateWithZeroMonths(){
        $dateToCheck['y'] = 2012; $dateToCheck['m'] = 00; $dateToCheck['d'] = 07;
        $dateExpected['y'] = 2012; $dateExpected['m'] = 01; $dateExpected['d'] = 07;
        $this->assertEquals($dateExpected, $this->datetime->check_date($dateToCheck));
    }

    public function testCheckDateReturnsImprovedDateWithFourteenMonths(){
        $dateToCheck['y'] = 2012; $dateToCheck['m'] = 14; $dateToCheck['d'] = 07;
        $dateExpected['y'] = 2012; $dateExpected['m'] = 12; $dateExpected['d'] = 07;
        $this->assertEquals($dateExpected, $this->datetime->check_date($dateToCheck));
    }

    public function testCheckDateReturnsImprovedDateWithZeroDays(){
        $dateToCheck['y'] = 2012; $dateToCheck['m'] = 07; $dateToCheck['d'] = 0;
        $dateExpected['y'] = 2012; $dateExpected['m'] = 07; $dateExpected['d'] = 1;
        $this->assertEquals($dateExpected, $this->datetime->check_date($dateToCheck));
    }

    public function testCheckDateReturnsImprovedDateWithThirtyFiveDays(){
        $dateToCheck['y'] = 2012; $dateToCheck['m'] = 07; $dateToCheck['d'] = 35;
        $dateExpected['y'] = 2012; $dateExpected['m'] = 07; $dateExpected['d'] = 31;
        $this->assertEquals($dateExpected, $this->datetime->check_date($dateToCheck));
    }

    public function testCheckDateReturnsImprovedDateWithDateFromFebruary(){
        $dateToCheck['y'] = 2012; $dateToCheck['m'] = 02; $dateToCheck['d'] = 31;
        $dateExpected['y'] = 2012; $dateExpected['m'] = 02; $dateExpected['d'] = 29;
        $this->assertEquals($dateExpected, $this->datetime->check_date($dateToCheck));
    }

    public function testGetDateReturnsTheDateWithDefaultWebsiteProtection(){
        $protect = new WebsiteProtection();
        $dateExpected['d'] = date('d');
        $dateExpected['m'] = date('m');
        $dateExpected['y'] = date('Y');
        $dateExpected['Ym'] = $dateExpected['y'] . $dateExpected['m'];
        $dateExpected['Ymd'] = $dateExpected['y'] . $dateExpected['m'] . $dateExpected['d'];

        $this->assertEquals($dateExpected, $this->datetime->get_date($protect));
    }

    public function testGetDateReturnsTheDateWithParameterFieldBeingEmpty(){
        $protect = new WebsiteProtection();
        $dateExpected['d'] = date('d');
        $dateExpected['m'] = date('m');
        $dateExpected['y'] = date('Y');
        $dateExpected['Ym'] = $dateExpected['y'] . $dateExpected['m'];
        $dateExpected['Ymd'] = $dateExpected['y'] . $dateExpected['m'] . $dateExpected['d'];

        $this->assertEquals($dateExpected, $this->datetime->get_date($protect, ''));
    }

    public function testGetDateReturnsTheDateWithParameterFieldBeingNull(){
        $protect = new WebsiteProtection();
        $dateExpected['d'] = date('d');
        $dateExpected['m'] = date('m');
        $dateExpected['y'] = date('Y');
        $dateExpected['Ym'] = $dateExpected['y'] . $dateExpected['m'];
        $dateExpected['Ymd'] = $dateExpected['y'] . $dateExpected['m'] . $dateExpected['d'];

        $this->assertEquals($dateExpected, $this->datetime->get_date($protect, null));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetDateReturnsTheDateWithParameterFieldBeingSet(){
        $protect = new WebsiteProtection();
        $_GET['d'] = 20120504;
        $dateExpected['d'] = '04';
        $dateExpected['m'] = '05';
        $dateExpected['y'] = '2012';
        $dateExpected['Ym'] = $dateExpected['y'] . $dateExpected['m'];
        $dateExpected['Ymd'] = $dateExpected['y'] . $dateExpected['m'] . $dateExpected['d'];

        $this->assertEquals($dateExpected, $this->datetime->get_date($protect));
    }

    public function testFormatDateReturnsACorrectlyFormattedDate(){
        $this->assertEquals('31-05-2012', class_datetime::formatDate('20120531'));
    }

    public function testFormatDateReturnsQuestionMarkWithDateBeingEmpty(){
        $this->assertEquals('?', class_datetime::formatDate(''));
    }

    public function testFormatDateReturnsQuestionMarkWithDateBeingZero(){
        $this->assertEquals('?', class_datetime::formatDate(0));
    }

    public function testFormatDateReturnsQuestionMarkDueToLengthBeingLessThanEight(){
        $this->assertEquals('201205', class_datetime::formatDate('201205'));
    }

    public function testFormatDateReturnsQuestionMarkDueToLengthBeingMoreThanEight(){
        $this->assertEquals('2012053112', class_datetime::formatDate('2012053112'));
    }
}