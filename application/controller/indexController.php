<?php

class indexController {
	
	public function _init() {
		
		$this->view = new stdClass();
		
	}
	
	public function indexAction() {
		
		/*$view = new view('index.phtml');
		$view->title = "This is a title";
		$view->message = "Finally, some light!";
		$view->render();*/
		
		$this->view->title = "This is a title";
		$this->view->message = "This is a message";
		
		return $this;
		
	}
	
}