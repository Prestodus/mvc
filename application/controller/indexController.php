<?php

class indexController extends Base_Controller {
	
	public function indexAction() {
		
		$this->view->title = "Floris Thijs - Visual Effects Artist";
		
		$this->view->images = glob('graphics/layout/slider/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
		
		return $this;
		
	}
	
	public function contactAction() {
		$this->view->title = "Contact - Floris Thijs - Visual Effects Artist";
		
		if ($this->postVars()) {
			$error = array();
			if (strlen($this->postVar('name')) < 3) $error["name"] = true;
			if (!filter_var($this->postVar('emailaddress'), FILTER_VALIDATE_EMAIL)) $error["emailaddress"] = true;
			if (strlen($this->postVar('message')) < 5) $error["message"] = true;
			if (count($error) < 1) {
				$mail = new scripts_Mail();
				$send = $mail->setFrom($this->config["contact"]["email"])
					->setTo('rubenc.android@gmail.com')
					->setBcc($this->postVar('emailaddress'))
					->setSubject('Contact through floristhijs.be')
					->setBody(
'Name:
 '.$this->postVar('name').'
Date:
 '.date('d/m/Y').'

========== Message ==========
'.$this->postVar('message').'
========== Message =========='
					)
					->send();
				if ($send === true) $this->view->success = 1;
			} else {
				$this->view->error = $error;
			}
		}
		
		return $this;
		
	}

}