<?php

class MyDate
{
    public $date;
    public $format;

    const TIMEZONE_DEFAULT = "UTC";

    protected $_workDays;

    protected static $_self = null;

    public static function obj()
    {
        if (empty(self::$_self)) self::$_self = new MyDate();
        return self::$_self;
    }

    public static function isDatePassed($d1, $d2 = null)
    {
        if (empty($d1)) return false;

        $timeSt1 = self::toTime($d1);
        $timeSt2 = (empty($d2)) ? time() : self::toTime($d2);

        if (empty($timeSt1)) return false;
        if (empty($timeSt2)) return false;

        return ( $timeSt1 <= $timeSt2 );
    }

    public static function toTime($d)
    {
        if (empty($d)) return time();
        return strtotime(self::replaceSymbols($d));
    }

    public static function replaceSymbols($d)
    {
        return str_replace('.', '-', str_replace('/', '-', $d));
    }

    public static function getAdjustedByDays($days = 0, $format = null)
    {
        if (empty($format) || !is_string($format)) $format = "Y-m-d H:i:s";
        return date($format, (time() + (86400*(int)$days)));
    }

    public function __construct($d = null)
    {
        $this->format = "Y-m-d H:i:s";
        if (!is_string($d) || empty($d)) $d = date($this->format);
        $d = MyDate::replaceSymbols($d);
        $this->_workDays = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
        $this->date = $d;
    }

    public function isWorkDay($date = null)
    {
        if (!is_string($date) || empty($date)) $date = $this->date;
        return in_array(date('N', strtotime($date)), $this->_workDays);
    }

    public function isWeekendDay($date = null)
    {
        return !$this->isWorkDay($date);
    }

    public function getNextDay($format = null, $date = null, $num = 1)
    {
        if (!is_string($date) || empty($date)) $date = $this->date;
        if (!is_string($format) || empty($format)) $format = $this->format;
        if (!is_numeric($num) || empty($num)) $num = 1;

        $nextTime = 60*60*24*$num + MyDate::toTime($date);

        return date($format, $nextTime);
    }

    public function getNextWorkDay($format = null, $date = null, $num = 1)
    {
        $nextDay = $this->getNextDay($format, $date, $num);

        if (!$this->isWorkDay($nextDay)) $nextDay = $this->getNextWorkDay($format, $nextDay, 1);

        return $nextDay;
    }

    public function getNextWeekendDay($format = null, $date = null, $num = 1)
    {
        $nextDay = $this->getNextDay($format, $date, $num);

        if (!$this->isWeekendDay($nextDay)) $nextDay = $this->getNextWeekendDay($format, $nextDay, 1);

        return $nextDay;
    }

    public function formatTo($format, $date = null)
    {
        if (!is_string($format) || empty($format)) $format = $this->format;
        if (!is_string($date) || empty($date)) $date = $this->date;

        return date($format, MyDate::toTime($date));
    }

    public static function getPreviousDayStartDate()
    {
        return date("Y-m-d 00:00:00", time() - (24 * 60 * 60));
    }

    public static function getPreviousDayEndDate()
    {
        return date("Y-m-d 23:59:59", time() - (24 * 60 * 60));
    }

    public static function getPreviousWeekStartDate(DateTimeZone $timezone = null)
    {
        $dateObj = new DateTime('previous week monday', $timezone);
        return $dateObj->format("Y-m-d 00:00:00");
    }

    public static function getPreviousWeekEndDate(DateTimeZone $timezone = null)
    {
        $dateObj = new DateTime('previous week sunday', $timezone);
        return $dateObj->format("Y-m-d 23:59:59");
    }

    public static function getPreviousMonthStartDate($timezone = null)
    {
        $dateObj = new DateTime('now', $timezone);
        $dateObj->sub( new DateInterval ( "P1M" ) );
        return $dateObj->format("Y-m-01 00:00:00");
    }

    public static function getPreviousMonthEndDate($timezone = null)
    {
        $previousMonthStartDate = self::getPreviousMonthStartDate($timezone);
        $lastDay = date("t", strtotime($previousMonthStartDate));
        return date("Y-m-" . $lastDay . " 23:59:59", strtotime($previousMonthStartDate));
    }

    public static function getSafe($date, $format = null)
    {
        if (!is_string($date) || empty($date)) return false;
        if (!is_string($format) || empty($format)) $format = "Y-m-d H:i:s";

        return date($format, self::toTime($date));
    }

    public static function isDateNotEmpty($date)
    {
        return (!empty($date) && $date != '0000-00-00 00:00:00' && $date != '0000-00-00')
            ? true
            : false;
    }

    public static function getDurationByTime($time, $format = "%a days %H:%I:%S")
    {
        if ((int)$time <= 86400) $format = "%H:%I:%S";

        $assignedDT = new DateTime(date("Y-m-d H:i:s", (int)$time));
        $decisionDT = new DateTime(date("Y-m-d H:i:s", 0));
        $interval = $assignedDT->diff($decisionDT);

        return $interval->format($format);
    }

    public static function getExecutionTime()
    {
        return self::getMicrotime() - STARTTIME;
    }

    public static function getMicrotime()
    {
        return microtime(true);
    }

}
