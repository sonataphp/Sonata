<?
//  STFile.php
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