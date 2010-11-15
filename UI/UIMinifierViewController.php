<?
//  UIMinifierViewController.php
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

class UIMinifierViewController extends UIViewController {
    
    private function concact($extension, $type) {
        if (strpos($this->params['files'], $extension) === FALSE) return;
        header("Expires: Fri, 01 Jan ".(date("Y")+5)." 05:00:00 GMT");
        
        $s = base64_decode(str_replace($extension, "", $this->params['files']));
        if (file_exists(CFAppPath."Cache/Media/".md5($this->params['files']).$extension)) {
            $last_modified_time = filemtime(CFAppPath."Cache/Media/".md5($this->params['files']).$extension);
            $etag = md5_file(CFAppPath."Cache/Media/".md5($this->params['files']).$extension);
            
            header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
            header("Etag: $etag");
            
            if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time ||
                trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
                header("HTTP/1.1 304 Not Modified");
                exit();
            } 
         
            
            $f = new STFile(CFAppPath."Cache/Media/".md5($this->params['files']).$extension);
            echo $f->read();
            exit();
        }
        $files = explode(",", $s);
        $content = '';
        foreach ($files as &$file) {
            $filename = UIApplication::sharedApplication()->settings->paths->$type.$file;
            $f = new STFile($filename);
            $content.= $f->read()."\r\n";
        }
        $f = new STFile(CFAppPath."Cache/Media/".md5($this->params['files']).$extension);
        $f->data = $content;
        $f->save();
        echo $content;
    }
    
    public function scriptsGeneratorAction() {
        header("Content-type: text/javascript");
        $this->concact(".js", "javascripts");
    }
    
    public function stylesGeneratorAction() {
        header("Content-type: text/css");
        $this->concact(".css", "styles");
    }
    
}

?>