<?

class UIViewCache extends STFileCache {
    
    private static $templates;
    
    public static function cacheTemplates($args) {
        self::$templates = func_get_args();
        self::configure(CFAppPath."Cache/Templates/");
    }
    
    private function inTemplates($template) {
        if (!self::$templates) return;
        foreach (self::$templates as $tpl) {
            if ($tpl == $template) return true;
        }
        return false;
    }
    
    public static function needToCacheTemplate($template) {
        
        if (STFileCache::exists($template)) return false;
        
        return self::inTemplates($template);
    }
    
    public static function setTemplate($templateFile, $data) {
        self::set($templateFile, $data);
    }
    
    public static function getTemplate($templateFile) {
        return self::keyFile($templateFile);
    }
    
    public static function isCached($file) {
        return (self::exists($file));
    }
    
    public static function flush() {
      $files = glob(CFAppPath."Cache/Templates/*.*");
      if ($files)
      foreach($files as $v){
            unlink($v);
      }
    }
    
}

?>