<?php

class documentationController extends Base_Controller {
	
	public function indexAction() {
		
		$this->view->title = 'Documentation - Index';
		$this->view->gets = $this->getVars();
		
		$this->redirect();
		
		return $this;
		
	}
	
}