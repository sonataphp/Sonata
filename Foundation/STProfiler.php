<?
//  STProfiler.php
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

class STProfiler {
    
    private static $enabled = false;
    private static $current;
    private static $startTime;
    private static $endTime;
    private static $options;
    private static $log;
	private static $_tolog = false;
	private static $is_profiling = false;
    
    private function getTimeStamp() {
	    $timeofday = gettimeofday();
        return 1000*($timeofday['sec'] + ($timeofday['usec'] / 1000000));
    }
    
    private function getElapsedTime($start, $end) {
	    return number_format(($end)-($start), 2);
    }
      
    public static function init($options = array()) {
        self::$enabled=true;
        self::$options=$options;
        self::$startTime=self::getTimeStamp();
		self::$is_profiling = true;
    }
    
    public static function disable() {
        self::$enabled=false;
    }
	
	public static function resume() {
		if (!self::$is_profiling) return;
        self::$enabled=true;
    }
    
    public static function exception($info) {
        self::add($info, 'Exception');
    }
    
    public static function add($info, $type) {
        self::start($info, $type);
        self::end();
    }
    
    public static function start($info, $type) {
        if (!self::$enabled) return;
        self::$current['startTime']=self::getTimeStamp();
        self::$current['info']=$info;
        self::$current['type']=$type;
    }
    
	public static function setWriteToLog($tolog = true) {
		self::$_tolog = $tolog;
	}
	
    public static function end() {
        if (!self::$enabled) return;
        if ((!self::$options['includes']) && (self::$current['type'] == 'Include')) return;
        if ((!self::$options['sql']) && (self::$current['type'] == 'SQL query')) return;
        if ((!self::$options['tz']) && (self::$current['type'] == 'TZ')) return;
        self::$current['endTime']=self::getTimeStamp();
        $dif=self::getElapsedTime(self::$current['startTime'], self::$current['endTime']);
        self::$log.='<tr><td'.((self::$current['type'] == 'Exception')?' style="background: #ff5044"':'').'><pre>'.self::$current['info'].'</pre></td><td'.((self::$current['type'] == 'Exception')?' style="background: #ff5044"':'').'>'.self::$current['type'].'</td><td'.((self::$current['type'] == 'Exception')?' style="background: #ff5044"':'').'>'.$dif.' ms.</td></tr>';
		if (self::$_tolog) {
			STLog::write("STProfiler	".self::$current['type']."	".self::$current['info']."	".$dif." ms");
		}
	}

    public static function outputToScreen() {
        if (!self::$enabled) return;
        self::$endTime=self::getTimeStamp();
        
        echo '
            <style type="text/css">
               .profiler-scheme {
              width: 100%;
              background: #FFF;
              border: 1px solid #000;
              position: absolute;
              z-index: 5000;
              border-collapse: collapse;
               }
               .profiler-scheme th {
              background: #f4f4f4;
              font-weight: bold;
               }
               .profiler-scheme th, .profiler-scheme td{
                   text-align: left;
               border: 1px solid #000;
               padding: 2px;
               padding-left: 5px;
               padding-right: 5px;
               font-size: 12px;
               font-family: Arial;
               }
               .profiler-scheme pre {
              font-size: 12px;
              font-family: Arial;
               }
            </style>
        ';
        echo '<table class="profiler-scheme"><thead>';
        echo '<tr><th>Action</th><th style="width: 80px">Type<th style="width: 80px">Time</th></tr></thead><tbody>';
        echo '<tr><td>Get Total</td><td>Total</td><td>'.self::getElapsedTime(self::$startTime, self::$endTime).' ms.</td></tr>';
        echo self::$log.'</tbody></table>';
    }

}

?>