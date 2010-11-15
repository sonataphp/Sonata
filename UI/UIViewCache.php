<?
//  UIViewCache.php
//  Sonata/UI
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