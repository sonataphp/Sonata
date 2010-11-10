<?
//
//  STFile.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STFile extends STObject {
    
    private $fileName;
    public $data;
    
    public function getFullPath() {
        return $this->fileName;
    }
    
    public function getFileName() {
        return basename($this->fileName);
    }
    
    public function getPath() {
        return rtrim(pathinfo($this->fileName, PATHINFO_DIRNAME), "/\\");
    }
    
    public function getExtension() {
        $path = $this->fileName;
        $qpos = strpos($path, "?");
        if ($qpos !== false) $path = substr($path, 0, $qpos); 
        return pathinfo($path, PATHINFO_EXTENSION);
    }
    
    public function getSize() {
        return filesize($this->fileName);
    }
    
    public function getPermissions() {
        return fileperms($this->fileName);
    }
    
    public function getLastAccessTime() {
        return fileatime($this->fileName);
    }
    
    public function getModificationTime() {
        return filemtime($this->fileName);
    }
    
    public function getFileGroup() {
        return filegroup($this->fileName);
    }
    
    public function getFileOwner() {
        return fileowner($this->fileName);
    }
    
    public function isReadable() {
        return is_readable($this->fileName);
    }
    
    public function isWriteable() {
        return is_writable($this->fileName);
    }
    
    public function read() {
        $this->data = file_get_contents($this->fileName);
        return $this->data;
    }
    
    public function write($data, $path = '') {
        $this->data = $data;
        $this->save($path);
    }
    
    public function save($path = '') {
        return file_put_contents(($path == '')?$this->fileName:$path, $this->data);
    }
    
    public function append($path = '') {
        return file_put_contents(($path == '')?$this->fileName:$path, $this->data, FILE_APPEND);
    }
    
    public function delete() {
        @unlink($this->fileName);
    }
    
    public function exists() {
        return (bool) file_exists($this->fileName);
    }
    
    public function __construct($fileName) {
        $this->fileName = $fileName;
    }
    
}

function STFile($filename) {
    $file = new STFile($filename);
    if (!$file->exists()) return null;
    return $file;
}

?>