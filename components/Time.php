<?php
class Time
{

    /**
     * Returns true if given date is today.
     *
     * @param string $date Unix timestamp
     * @return boolean True if date is today
     */
    public static function isToday($date)
    {
        return date('Y-m-d', $date) == date('Y-m-d', time());
    }

    /**
     * Returns true if given date was yesterday
     *
     * @param string $date Unix timestamp
     * @return boolean True if date was yesterday
     */
    public static function wasYesterday($date)
    {
        return date('Y-m-d', $date) == date('Y-m-d', strtotime('yesterday'));
    }

    /**
     * Returns true if given date is in this year
     *
     * @param string $date Unix timestamp
     * @return boolean True if date is in this year
     */
    public static function isThisYear($date)
    {
        return date('Y', $date) == date('Y', time());
    }

    /**
     * Returns true if given date is in this week
     *
     * @param string $date Unix timestamp
     * @return boolean True if date is in this week
     */
    public static function isThisWeek($date)
    {
        return date('W Y', $date) == date('W Y', time());
    }

    /**
     * Returns true if given date is in this month
     *
     * @param string $date Unix timestamp
     * @return boolean True if date is in this month
     */
    public static function isThisMonth($date)
    {
        return date('m Y', $date) == date('m Y', time());
    }

    public static function secondsToDifference($seconds)
    {
        $diff = $seconds;
        if ($h=intval((floor($diff/3600)))) {
            $diff = $diff % 3600;
        }
        
        if ($m=intval((floor($diff/60)))) {
            $diff = $diff % 60;
        }
        
        $s = intval($diff);


        $ret = $h ? $h . ':' : '';
        $ret .= sprintf('%02d', $m) . ':' . sprintf('%02d', $s);
        return $ret;
    }
}
