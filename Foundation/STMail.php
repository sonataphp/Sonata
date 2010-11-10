<?
//
//  STMail.php
//  Sonata/Foundation
//
//  Created by Dan Sosedoff on 6/10/2010.
//

class STMail {
	private $to, $from, $replyto, $mailedby, $returnpath;
	private $subject, $data;
	private $format = 'text/html';
	private $charset = 'utf-8';
	private $buffer;
	private $body;
	private $use_body = false;
	
	public function __construct() {
		$this->to = array();
		$this->subject = 'No subject';
		$this->data = array();
	}
	
	public function setSubject($value) { $this->subject = $value; }
	public function setTo($email) { $this->to[] = $email; }
	public function setFrom($email) { $this->from = $email; }
	public function setReplyTo($email) { $this->replyto = $email; }
	public function setMailedBy($value) { $this->mailedby = $value; }
	public function setReturnPath($value) { $this->returnpath = $value; }
	public function setFormat($value) { $this->format = $value; }
	public function setData($key, $value) { $this->data[$key] = $value; }
	public function setBody($value) { $this->body = $value; $this->use_body = true; }
	
	public function send() {
        $to_array = array_unique($this->to);
        $to = implode(',', $to_array);
    
        $headers  = "from: ".$this->from."\r\n";
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