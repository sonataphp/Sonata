<?php
//  STMail.php
//  Sonata/Foundation
//
// Copyright 2010 Roman Efimov <romefimov@gmail.com>
// 				  Dan Sosedoff <http://blog.sosedoff.com>
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

define ("STMailFormatHtml", "text/html");
define ("STMailFormatPlain", "text/plain");

class STMail {
	private $to, $from, $replyto, $mailedby, $returnpath;
	private $subject;
	private $format = STEmailFormatHtml;
	private $charset = 'utf-8';
	private $buffer;
	private $body;
	private $fromName;
	
	public function __construct() {
		$this->to = array();
		$this->subject = 'No subject';
	}
	
	public function setSubject($subject) { $this->subject = $subject; return $this; }
	public function setTo($email) { $this->to[] = $email; return $this; }
	public function setFrom($email, $fromName = '') { $this->from = $email; $this->fromName = $fromName; return $this; }
	public function setReplyTo($email) { $this->replyto = $email; return $this; }
	public function setMailedBy($mailedBy) { $this->mailedby = $mailedBy; return $this; }
	public function setReturnPath($email) { $this->returnpath = $email; return $this; }
	public function setFormat($format) { $this->format = $format; return $this; }
	public function setBody($body) { $this->body = $body; return $this; }
	
	public function send() {
        $to_array = array_unique($this->to);
        $to = implode(',', $to_array);
    
        $headers  = "from: ".$this->from."\r\n";
		if (!empty($this->fromName)) $headers  = "from: ".$this->fromName." <".$this->from.">\r\n";
        if (!empty($this->replyto)) $headers .= "reply-to: ".$this->replyto."\r\n";
        if (!empty($this->returnpath)) $headers .= "return-path: ".$this->returnpath."\r\n";
        if (!empty($this->mailedby)) $headers .= "mailed-by: ".$this->mailedby."\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "content-type: ".$this->format."; charset=".$this->charset;
    
        return @mail($to, $this->subject, $this->body, $headers);
		return false;
	}
}

?>