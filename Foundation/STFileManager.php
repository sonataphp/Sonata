<?
//  STFileManager.php
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

class STFileManager extends STObject {
    
    final private function __construct() {}
    final private function __clone() {}
    
    public static function applicationPath() {
        return rtrim(CFAppPath, "/\\");
    }
    
    public static function createFileAtPath($path, $contents) {
        file_put_contents($path, $contents);
    }
    
    public static function appendFileAtPath($path, $contents) {
        file_put_contents($path, $contents, FILE_APPEND);
    }
    
    public static function deleteFile($path) {
        unlink($path);
    }
    
    public static function deleteFolder($path) {
        rmdir($path);
    }
    
    public static function createFolder($path, $mode) {
        mkdir($path, $mode, true);
    }
    
    public static function copyFile($sourcePath, $destPath) {
        copy($sourcePath, $destPath);
    }
    
    public static function moveFile($sourcePath, $destPath) {
        rename($sourcePath, $destPath);
    }
    
    public static function changeMode($fileName, $mode) {
        chmod($fileName, $mode);
    }
    
    public static function uploadFile($source, $dest) {
        return move_uploaded_file($source, $dest);
    }
    
    public static function openFile($filePath) {
        $file = new STFile($filePath);
        if ($file->exists())
            $file->read();
        return $file;
    }

}

?>