<?
//  STFileCache.php
//  Sonata/Foundation
//
// Copyright 2010 Dan Sosedoff <http://blog.sosedoff.com>
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

class STFileCache {
    const DEFAULT_TTL = 86400; // one day
    private static $dir, $prefix;
  
    public static function configure($savedir='/tmp', $prefix='') {
        self::$dir = $savedir;
        self::$prefix = $prefix;
    }
    
    public static function keyFile($key) {
        return self::$dir.'/'.self::$prefix.md5($key).'.cache.php';
    }
    
    public static function exists($key, $ttl=self::DEFAULT_TTL) {
        $path = self::keyFile($key); 
        
        if (!file_exists($path)) return false;
        else {
          $diff = (filemtime($path) + $ttl) - time();
          if ($diff < 0) { @unlink($path); return false; }
          else { return true; }
        }
    }
    
    public static function get($key, $ttl=self::DEFAULT_TTL) {
        if (!self::exists($key, $ttl)) return null;
        $path = self::keyFile($key);
        $fh = fopen($path, 'rb');
        $data = fread($fh, filesize($path)); fclose($fh);
        return $data;
    }
    
    public static function set($key, $data) {
        $fh = fopen(self::keyFile($key), 'wb');
        if ($fh) {
            fwrite($fh, $data);
            fflush($fh);
            fclose($fh);
        }
    }
}

?>