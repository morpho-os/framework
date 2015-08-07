<?php
namespace Morpho\Base;

class DateTime extends \DateTimeImmutable {
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

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
        if (strlen($val) > 1 && $val[0] == 0) {
            $val = substr($val, 1);
        }
        return $val;
    }

    /**
     * @return int
     */
    public function getNumberOfDaysInMonth() {
        $month = $this->month();
        if (substr($month, 0, 1) == '0' && strlen($month) == 2) {
            $month = substr($month, 1);
        }
        $lastDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        if ($this->isLeapYear()) {
            $lastDays[1] = 29;
        }
        return $lastDays[$month - 1];
    }

    public static function createFromFormat($format, $value, $timeZone = null) {
        return new static(
            parent::createFromFormat($format, $value)
                ->format(self::DATETIME_FORMAT)
        );
    }

    /**
     * @return string
     */
    public function formatDateTime() {
        return $this->format(self::DATETIME_FORMAT);
    }

    public function getTimestamp() {
        return PHP_INT_SIZE === 4 ? $this->format('U') : parent::getTimestamp();
    }

    /**
     * @param string|int $value
     * @return bool
     */
    public static function isTimestamp($value) {
        return is_numeric($value) && preg_match('~^\d+$~s', $value) && strlen($value) === 10;
    }

    public static function createFromTimestamp($timestamp) {
        return (new static())->setTimestamp($timestamp);
    }
}
