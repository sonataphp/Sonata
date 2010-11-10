<?
//
//  STVideo.php
//  Sonata/Foundation
//
//  Created by Dan Sosedoff on 6/10/2010.
//

abstract class STVideoInfo {
	protected $code;
	protected $embed;
	protected $title;
	protected $description;
	protected $thumbnail;
	protected $type;
	public $has_embed;
	
	public function __construct($str) { 
		$this->embed = $str;
		$this->has_embed = false;
		if (!$this->process()) {
			throw new Exception('Invalid Video');
		}
	}
	protected function process() { }
	public function getTitle() { return $this->title; }
	public function getThumbnail() { return $this->thumbnail; }
	public function getDescription() { return $this->description; }
	public function getType() { return $this->type; }
	public function getCode() { return $this->code; }
}


class STYouTube extends STVideoInfo {
	protected $type = 'youtube';
	
	private function fetchData($url) {
		$buffer = file_get_contents($url);
		if ($buffer) {
			$xml = new SimpleXMLElement($buffer);
			return $xml;
		}
		return false;
	}
	
	protected function process() { 
		$matches = array();
	
		if (preg_match('/watch\?v=([a-z0-9_\-]{1,})/i', $this->embed, $matches) ||
				preg_match('/youtube.com\/v\/([a-z0-9_\-]{1,})/i', $this->embed, $matches)) {
			
			$this->code = $matches[1];
			$this->thumbnail = "http://img.youtube.com/vi/".$matches[1]."/0.jpg";
			$xml = $this->fetchData('http://gdata.youtube.com/feeds/api/videos/'.$matches[1]);
			if ($xml) { 
				$this->title = (string)$xml->title;
				$this->description = (string)$xml->content;
				return true;
			}
		}
		
		return false;
	}
}

class STVimeo extends STVideoInfo {
	protected $type = 'vimeo';
	
	private function fetchData($url) {
		$buffer = file_get_contents($url);
		if ($buffer) {
			$info = unserialize($buffer);
			return $info[0];
		}
		return false;
	}
	
	protected function process() {
		$matches = array();
		if (preg_match('/http:\/\/vimeo.com\/([\d]+)/i', $this->embed, $matches)) {
			$this->code = $matches[1];
			$info = $this->fetchData('http://vimeo.com/api/v2/video/'.$matches[1].'.php');
			if (is_array($info)) {
				$this->title = $info['title'];
				$this->description = $info['caption'];
				$this->thumbnail = $info['thumbnail_large'];
				return true;
			}
		}
		
		return false;
	}
}


class STVideo {
	public static function identify($str) {
		$match_map = array(
			'youtube' => 'STYouTube',
			'vimeo.com' => 'STVimeo'
		);
		
		$obj = false;
		foreach($match_map as $key => $class_name) {
			if (strpos($str, $key) > 0) {
				$obj = new $class_name($str);
				if (strpos($str, "embed") > 0) $obj->has_embed = true;
				break;
			}
		}
		
		return $obj;
	}
}

?>
