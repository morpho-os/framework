<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use DateTimeImmutable;
use DateTimeZone;

class DateTime extends DateTimeImmutable {
    public const MYSQL_DATETIME = 'Y-m-d H:i:s';
    public const MYSQL_DATETIME_RE = '~^(?<year>\d{4})-(?<month>\d{2})-(?<day>\d{2}) (?<hour>\d{2}):(?<min>\d{2}):(?<sec>\d{2})$~s';

    /**
     * @param null|string $time
     * @param null|string|DateTimeZone $timeZone
     */
    public function __construct(string $time = null, $timeZone = null) {
        if (null === $time) {
            $time = 'now';
        }
        if (\is_string($timeZone)) {
            $timeZone = new DateTimeZone($timeZone);
        }
        parent::__construct($time, $timeZone);
    }

    public static function now($timeZone = null): string {
        return (new static(null, $timeZone))->mySqlDateTime();
    }

    public function yearAsInt() {
        return (int)$this->format('Y');
    }

    public function year() {
        return $this->format('Y');
    }

    /**
     * @return int
     */
    public function monthAsInt() {
        return (int)$this->format('n');
    }

    public function month() {
        return $this->format('m');
    }

    /**
     * @return int
     */
    public function dayAsInt() {
        return (int)$this->format('j');
    }

    public function day() {
        return $this->format('d');
    }

    /**
     * @return int
     */
    public function hourAsInt() {
        return (int)$this->format('G');
    }

    public function hour() {
        return $this->format('H');
    }

    /**
     * @return int
     */
    public function minuteAsInt() {
        return (int)$this->stripLeadingZero($this->format('i'));
    }

    public function minute() {
        return $this->format('i');
    }

    /**
     * @return int
     */
    public function secondAsInt() {
        return (int)$this->stripLeadingZero($this->format('s'));
    }

    public function second() {
        return $this->format('s');
    }


    public function isLeapYear() {
        $year = $this->year();
        return $year % 400 == 0 || ($year % 100 != 0 && $year % 4 == 0);
    }

    /**
     * @param string $val
     * @return string
     */
    protected function stripLeadingZero($val) {
        if (\strlen($val) > 1 && $val[0] == 0) {
            $val = \substr($val, 1);
        }
        return $val;
    }

    /**
     * @return int
     */
    public function numberOfDaysInMonth() {
        $month = $this->month();
        if (\substr($month, 0, 1) == '0' && \strlen($month) == 2) {
            $month = \substr($month, 1);
        }
        $lastDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        if ($this->isLeapYear()) {
            $lastDays[1] = 29;
        }
        return $lastDays[$month - 1];
    }

    /**
     * Overridden to return self.
     * @param string $format
     * @param string $value
     * @param null|string|\DateTimeZone $timeZone
     * @return DateTimeImmutable|false|DateTime
     */
    public static function createFromFormat($format, $value, $timeZone = null): self {
        return new static(
            parent::createFromFormat($format, $value, $timeZone)->format(self::ISO8601)
        );
    }

    public function mySqlDateTime(): string {
        return $this->format(self::MYSQL_DATETIME);
    }

    public function getTimestamp(): int {
        return PHP_INT_SIZE === 4 ? (int)$this->format('U') : parent::getTimestamp();
    }

    /**
     * @param string|int $value
     * @return bool
     */
    public static function isTimestamp($value): bool {
        $value = (string)$value;
        return \is_numeric($value) && \preg_match('~^\d+$~s', $value) && \strlen($value) === 10;
    }

    public static function mkFromTimestamp($timestamp): self {
        return (new static())->setTimestamp($timestamp);
    }
    
    public function __toString() {
        return $this->mySqlDateTime();
    }
}
