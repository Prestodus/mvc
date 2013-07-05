<?php

class scripts_Mail {
	
	public $from;
	public $to = false;
	public $bcc = false;
	public $subject;
	public $body;
	
	public $contentType = false;

	public function setFrom($from) {
		$this->from = $from;
		return $this;
	}
	public function getFrom() {
		return $this->from;
	}

	public function setTo($to) {
		$this->to = $to;
		return $this;
	}
	public function getTo() {
		return $this->to;
	}
	public function addTo($to) {
		if (!$this->to) {
			if (is_array($to)) {
				$this->to = $to;
			} else {
				$this->to = array($to);
			}
		} else {
			if (is_array($this->to)) {
				if (is_array($to)) {
					foreach ($to as $add) {
						$this->to[] = $add;
					}
				} else {
					$this->to[] = $to;
				}
			} else {
				$oldto = $this->to;
				if (is_array($to)) {
					$this->to = $to;
					$this->to[] = $oldto;
				} else {
					$this->to = array($oldto, $to);
				}
			}
		}
		return $this;
	}

	public function setBcc($bcc) {
		$this->bcc = $bcc;
		return $this;
	}
	public function getBcc() {
		return $this->bcc;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}
	public function getSubject() {
		return $this->subject;
	}

	public function setBody($body) {
		$this->body = $body;
		return $this;
	}
	public function getBody() {
		return $this->body;
	}
	
	
	
	
	public function is_html($is_html = false) {
		if ($is_html) $this->contentType = 'text/html';
		else $this->contentType = 'text/plain';
		return $this;
	}
	
	
	
	
	public function send() {
		$error = array();
		if (!filter_var($this->getFrom(), FILTER_VALIDATE_EMAIL)) {
			$error[] = "From (".$this->getFrom().") is not a valid email address.";
		}
		if (is_array($this->getTo())) {
			foreach ($this->getTo() as $to) {
				if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
					$error[] = "To (".$to.") is not a valid email address.";
				}
			}
		} else {
			if (!filter_var($this->getTo(), FILTER_VALIDATE_EMAIL)) {
				$error[] = "To (".$this->getTo().") is not a valid email address.";
			}
		}
		if ($this->getBcc() != false && !filter_var($this->getBcc(), FILTER_VALIDATE_EMAIL)) {
			$error[] = "Bcc (".$this->getBcc().") is not a valid email address.";
		}
		if (strlen($this->getSubject()) > 78) {
			$error[] = "Subject (".$this->getSubject().") is too long (max 78 characters).";
		}
		if (strlen($this->getSubject()) < 1) {
			$error[] = "Subject (".$this->getSubject().") is too short (min 1 character).";
		}
		if (count($error) < 1) {
			$to = (is_array($this->getTo()))?implode(",", $this->getTo()):$this->getTo();
			$subject = str_ireplace(array("\r", "\n", '%0A', '%0D'), '', $this->getSubject());
			$body = str_replace("\n.", "\n..", $this->getBody());
			
			$headers = array();
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: ".$this->contentType."; charset=iso-8859-1";
			$headers[] = "From: Floris Thijs <".$this->getFrom().">";
			$headers[] = "Reply-To: Floris Thijs <".$this->getFrom().">";
			if ($this->getBcc()) $headers[] = "Bcc: ".$this->getBcc();
			$headers[] = "X-Mailer: PHP/".phpversion();
			
			$mail = mail($to, $subject, $body, implode("\r\n", $headers));
			
			return $mail;
		} else {
			return $error;
		}
	}
	
}