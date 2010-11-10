<?
//
//  STFileManager.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STFileManager extends STObject{
    
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
        @unlink($path);
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
        @chmod($fileName, $mode);
    }
    
    public static function uploadFile($source, $dest) {
        return @move_uploaded_file($source, $dest);
    }
    
    public static function openFile($filePath) {
        $file = new STFile($filePath);
        if ($file->exists())
            $file->read();
        return $file;
    }

}

?>