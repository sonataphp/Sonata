<?
//
//  STUploadedFile.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STUploadedFile {
	public $file, $name, $type, $size, $path, $error;

	public function __construct($f){
		$this->file = $f;
		$this->name = $f['name'];
		$this->type = $f['type'];
		$this->size = $f['size'];
		$this->path = $f['tmp_name'];
		$this->error = $f['error'];
	}

	public function hasError(){
		return $this->isUploaded() && $this->error != UPLOAD_ERR_OK;
	}

	public function isUploaded(){
		return $this->error != UPLOAD_ERR_NO_FILE;
	}

	public function save($path){
		return STFileManager::uploadFile($this->path, $path);
	}
}


?>