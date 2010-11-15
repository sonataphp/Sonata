<?
//  STDate.php
//  Sonata/Foundation
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

class STDate extends STObject {
    
    private $timestamp;
    private $format;
    
    public function __get($property) {
        if ($property == 'stamp') return $this->timestamp;
    }
    
    public function __construct($format = 'Y-m-d H:i:s') {
        $this->format = $format;
    }
    
    public function dateWithString($str) {
        $this->timestamp = strtotime($str);
        return $this;
    }
    
    public function dateWithTimestamp($timestamp) {
        $this->timestamp = $timestamp;
        return $this;
    }
    
    public function setFormat($format) {
        $this->format = $format;
    }
    
    public function __toString() {
        return date($this->format, $this->timestamp);
    }
    
    public function isLeapYear() {
        return (bool) CFDate::isLeapYear(date("Y", $this->timestamp));
    }
    
    public function weekDaysRange() {
        CFDate::weekDaysRange($this->timestamp, $start_date, $end_date);
        return array($start_date, $end_date);
    }
    
    public function weekDaysSinceMonday() {
        return CFDate::weekDaysSinceMonday($this->timestamp);
    }
    
    public function timeSince($format = "M jS, Y") {
        return CFDate::timeSince($this->timestamp, $format);
    }
    
    public function asInt() {
        return $timestamp;
    }
    
    public function day() {
        return date("d", $this->timestamp);
    }
    
    public function month() {
        return date("n", $this->timestamp);
    }
    
    public function year() {
        return date("Y", $this->timestamp);
    }
    
    public function dayOfWeek() {
        return CFDate::dayOfWeek($this->timestamp);
    }
    
    public function firstDayOfMonth() {
        $timestamp = CFDate::firstDayOfMonth(date("m", $this->timestamp), date("Y", $this->timestamp));
        return STDate($this->format)->dateWithTimestamp($timestamp);
    }
    
    public function lastDayOfMonth() {
        $timestamp = CFDate::lastDayOfMonth(date("m", $this->timestamp), date("Y", $this->timestamp));
        return STDate($this->format)->dateWithTimestamp($timestamp);
    }
    
    // Add and substract dates
    
    public function addYears($years) {
        $this->timestamp = strtotime("+".abs($years)." years", $this->timestamp);
        return $this;
    }
    
    public function subYears($years) {
        $this->timestamp = strtotime("-".abs($years)." years", $this->timestamp);
        return $this;
    }
    
    public function addMonths($months) {
        $this->timestamp = strtotime("+".abs($months)." months", $this->timestamp);
        return $this;
    }
    
    public function subMonths($months) {
        $this->timestamp = strtotime("-".abs($months)." months", $this->timestamp);
        return $this;
    }
    
    public function addDays($days) {
        $this->timestamp = strtotime("+".abs($days)." days", $this->timestamp);
        return $this;
    }
    
    public function subDays($days) {
        $this->timestamp = strtotime("-".abs($days)." days", $this->timestamp);
        return $this;
    }
}

/*
 * @return STDate
 */
function STDate($format = 'Y-m-d H:i:s') {
    return new STDate($format);
}

function STDateNow($format = 'Y-m-d H:i:s') {
    return STDate($format)->dateWithTimestamp(strtotime(date("Y-m-d H:i:s")));
}

?>