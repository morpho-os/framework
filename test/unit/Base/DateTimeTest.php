<?php declare(strict_types=1);
namespace MorphoTest\Unit\Base;

use Morpho\Base\DateTime;
use Morpho\Test\TestCase;

class DateTimeTest extends TestCase {
    public function testIsImmutable() {
        $this->assertInstanceOf('\DateTimeImmutable', new DateTime());
    }

    public function testGetTimestampForLargeDates() {
        $this->assertEquals(2622808167, (new DateTime('2053-02-10 13:49:27'))->getTimestamp());
    }

    public function testDateAndTimePartsAsInt() {
        $dateTime = new DateTime('2012-01-02 05:02:09');
        $this->assertSame(2012, $dateTime->yearAsInt());
        $this->assertSame(1, $dateTime->monthAsInt());
        $this->assertSame(2, $dateTime->dayAsInt());
        $this->assertSame(5, $dateTime->hourAsInt());
        $this->assertSame(2, $dateTime->minuteAsInt());
        $this->assertSame(9, $dateTime->secondAsInt());
    }

    public function testFormatDateTime() {
        $date = '2042-02-10 12:11:43';
        $this->assertEquals($date, (new DateTime($date))->formatDateTime());
    }

    public function dataForTestIsTimestamp() {
        $timestamp = DateTime::createFromFormat('Y-m-d H:i:s', '2013-12-09 01:31:34')->getTimestamp();
        return [
            [
                123,
                false,
            ],
            [
                '123',
                false,
            ],
            [
                '12345678910',
                false,
            ],
            [
                $timestamp,
                true,
            ],
        ];
    }

    /**
     * @dataProvider dataForTestIsTimestamp
     */
    public function testIsTimestamp($value, $isTrue) {
        if ($isTrue) {
            $this->assertTrue(DateTime::isTimestamp($value));
        } else {
            $this->assertFalse(DateTime::isTimestamp($value));
        }
    }

    public function testNewFromTimestamp() {
        $timestamp = (new DateTime('2013-12-08 11:02:04'))->getTimestamp();
        $this->assertEquals($timestamp, DateTime::newFromTimestamp($timestamp)->getTimestamp());
    }

    public function testCreateFromFormatReturnsValidInstance() {
        $this->assertInstanceOf(DateTime::class, DateTime::createFromFormat('Y-m-d', '2013-06-21'));
    }

    public function testNumberOfDaysInMonth() {
        $format = 'Y-m-d';
        $this->assertEquals(29, DateTime::createFromFormat($format, '2008-02-01')->numberOfDaysInMonth());
        $this->assertEquals(28, DateTime::createFromFormat($format, '2009-02-01')->numberOfDaysInMonth());
        $this->assertEquals(31, DateTime::createFromFormat($format, '2009-01-01')->numberOfDaysInMonth());
        $this->assertEquals(31, DateTime::createFromFormat($format, '2009-08-01')->numberOfDaysInMonth());
        $this->assertEquals(31, DateTime::createFromFormat($format, '2009-08-01')->numberOfDaysInMonth());
        $this->assertEquals(30, DateTime::createFromFormat($format, '2009-09-01')->numberOfDaysInMonth());
    }
}
