<?php
//  CFDate.php
//  Sonata/CoreFoundation
//
// Copyright 2010 Roman Efimov <romefimov@gmail.com>
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//

class CFDate {
	
	final private function __construct() {}
    final private function __clone() {}
	
	/*
	 *  English representation of time passed since $date.
	 *
	 *  @param string $date String date.
	 *  @param string $format Format for output date (when the difference is more 4 days).
	 *  @return string English string representing time passed since $date.
	 */
	public static function timeSince($date, $format = "M jS, Y") {
		$dateDiff=time()-strtotime($date);
		$fullYears = floor($dateDiff/(60*60*24*365));
		$fullMonths = floor($dateDiff/(60*60*24*30));
		$fullDays = floor($dateDiff/(60*60*24));
		$fullHours = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
		$fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
		//echo "Differernce is $fullYears years, $fullMonths months, $fullDays days, $fullHours hours and $fullMinutes minutes.";
		if (($fullHours == 0) && ($fullDays == 0)) {
			if ($fullMinutes == 1) return "1 minute ago"; 
			if ($fullMinutes == 0) return "few seconds ago"; 
			if ($fullMinutes > 1) return $fullMinutes." minutes ago"; 
		}
		if (($fullHours > 0) && ($fullDays == 0)) {
			if ($fullHours == 1) return "1 hour ago"; 
			if ($fullHours > 1) return $fullHours." hours ago"; 
		}
		if (($fullDays > 0) && ($fullMonths == 0)) {
			if ($fullDays == 1) return "1 day ago";
			if ($fullDays == 2) return "2 days ago";
			if ($fullDays == 3) return "3 days ago";
			if ($fullDays > 3) return date("Y", strtotime($date) > 1970)?date($format, strtotime($date)):'';
		}
		if (($fullMonths > 0) && ($fullYears == 0)) {
			if ($fullMonths == 1) return date("Y", strtotime($date) > 1970)?date($format, strtotime($date)):'';
			if ($fullMonths > 1) return date("Y", strtotime($date) > 1970)?date($format, strtotime($date)):'';
		}
		if ($fullYears > 0) {
			if ($fullYears == 1) return date("Y", strtotime($date) > 1970)?date($format, strtotime($date)):'';
			if ($fullYears > 1) return date("Y", strtotime($date) > 1970)?date($format, strtotime($date)):'';
		}
	}
    
	/*
	 *  Returns array of weekdays past since Monday of a given date.
	 *
	 *  @param string $date String date.
	 *  @return array Array of days in Y-m-d format.
	 */
    public static function weekDaysSinceMonday($date) {
        // Assuming $date is in format DD-MM-YYYY
        $date = date("d-m-Y", strtotime($date));
        list($day, $month, $year) = explode("-", $date);
    
        // Get the weekday of the given date
        $wkday = date('l',mktime('0','0','0', $month, $day, $year));
    
        switch($wkday) {
            case 'Monday': $numDaysToMon = 0; break;
            case 'Tuesday': $numDaysToMon = 1; break;
            case 'Wednesday': $numDaysToMon = 2; break;
            case 'Thursday': $numDaysToMon = 3; break;
            case 'Friday': $numDaysToMon = 4; break;
            case 'Saturday': $numDaysToMon = 5; break;
            case 'Sunday': $numDaysToMon = 6; break;   
        }
    
        // Timestamp of the monday for that week
        $monday = mktime('0','0','0', $month, $day-$numDaysToMon, $year);
    
        $seconds_in_a_day = 86400;
    
        // Get date for 7 days from Monday (inclusive)
        for($i=0; $i<7; $i++) {
            $dates[$i] = date('Y-m-d',$monday+($seconds_in_a_day*$i));
        }
    
        return $dates;
    }
    
	/*
	 *  Calculates days between two dates.
	 *
	 *  @param timestamp $start Start date, timestamp.
	 *  @param timestamp $end End date, timestamp.
	 *  @return int Difference in days.
	 */
    public static function daysBetween($start, $end) {
        $diff = $end_ts - $start_ts;
        return round($diff / 86400);
    }
    
	/*
	 *  Returns first and last week days of the given date.
	 *
	 *  @param string $date String date.
	 *  @return array Array with 2 values: first and last week days.
	 */
    public static function weekDaysRange($date) {
        $dates = CFDate::weekDaysSinceMonday($date);
        $start_date = $dates[0];
        $end_date = $dates[6];
		return array($start_date, $end_date);
    }
    
	/*
	 *  Returns timestamp of the first day of the month.
	 *
	 *  @param int $month Month.
	 *  @param int $year Year.
	 *  @return timestamp Timestamp of the first day of the month.
	 */
    public static function firstDayOfMonth($month, $year) {
        return strtotime(date("$year-$month-01 0:00:00"));
    }
    
	/*
	 *  Returns timestamp of the last day of the month.
	 *
	 *  @param int $month Month.
	 *  @param int $year Year.
	 *  @return timestamp Timestamp of the last day of the month.
	 */
    public static function lastDayOfMonth($month, $year) {
		return mktime(23, 59, 59, ($month + 1), 0, $year);
    }
    
	/*
	 *  Returns the day of the year (starting from 0).
	 *
	 *  @param string $date String date.
	 *  @return int day of the year
	 */
    public static function dayOfYear($date) {
        echo date("z", mktime(0,0,0,date("m", strtotime($date)),date("d", strtotime($date)),date("Y", strtotime($date))))+1;
    }
    
	/*
	 *  Returns the day of the week (starting from 0). 0 is Sunday.
	 *
	 *  @param string $date String date.
	 *  @return int Int value, the day of the week.
	 */
    public static function dayOfWeek($date) {
		$date = strtotime($date);
        $year = date("Y", $date);
        $month = date("n", $date);
        $day = date("d", $date);
        if ((1901 < $year) and ($year < 2038)) {
            return (int) date('w', mktime(0, 0, 0, $month, $day, $year));
        }

        // gregorian correction
        $correction = 0;
        if (($year < 1582) or (($year == 1582) and (($month < 10) or (($month == 10) && ($day < 15))))) {
            $correction = 3;
        }

        if ($month > 2) {
            $month -= 2;
        } else {
            $month += 10;
            $year--;
        }

        $day  = floor((13 * $month - 1) / 5) + $day + ($year % 100) + floor(($year % 100) / 4);
        $day += floor(($year / 100) / 4) - 2 * floor($year / 100) + 77 + $correction;

        return (int) ($day - 7 * floor($day / 7));
    }
    
	/*
	 *  Checks if the year is leap.
	 *
	 *  @param int $year Year.
	 *  @return bool true if the year is leap, otherwise false.
	 */
    public static function isLeapYear($year) {
		$result = (($year%400 == 0) || ($year%4 == 0 && $year%100 != 0)) ? true : false;
		return (bool) $result;
    }
}

?>