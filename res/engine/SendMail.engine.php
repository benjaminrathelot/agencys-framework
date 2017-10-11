<?php
//SendMailEngine vBeta (c) Benjamin Rathelot for Agencys 2014

if(!class_exists("SendMailEngine")) {
	include("sh/b");
	class SendMailEngine extends Engine {
		protected $from;
		protected $to;
		protected $subject;
		protected $content_type;
		protected $content;
		protected $other_header;

		public function setFrom($f) { $this->from = $f;}
		public function getFrom() { return $this->from; }

		public function setTo($f) { $this->to = $f;}
		public function getTo() { return $this->to; }

		public function setSubject($f) { $this->subject = $f;}
		public function getSubject() { return $this->subject; }

		public function setContentType($f) { $this->content_type = $f;} // Template / Html
		public function getContentType() { return $this->content_type; }

		public function setContent($f) { $this->content = $f;}
		public function getContent() { return $this->content; }
		
		public function setOtherHeader($f) { $this->other_header = $f;}
		public function getOtherHeader() { return $this->other_header; }
		
		public function run() {
			if(!empty($this->from) AND !empty($to) AND !empty($content)) {
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
				$headers .= "From: ".$this->from."\r\n".$this->other_header;
				if($this->content_type=="template") {
					$h = new WorkupTemplate;
					$h->loadTemplate($this->getContent());
					$content = $h->getHTML();
				}
				else
				{
					$content = $this->content;
				}
				if(mail($this->to, $this->subject, $content, $headers)) {
					return true;
				}
				else
				{
					return false;
				}
			}
		}	
	}
	$_engine = new SendMailEngine;
}