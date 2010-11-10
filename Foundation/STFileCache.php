<?
//
//  STVideo.php
//  Sonata/Foundation
//
//  Created by Dan Sosedoff on 6/10/2010.
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