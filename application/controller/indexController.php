<?php

class indexController extends Base_Controller {
	
	public function indexAction() {
		
		$this->view->title = "Floris Thijs - Visual Effects Artist";
		
		$this->view->images = glob('graphics/layout/slider/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
		
		return $this;
		
	}
	
	public function portfolioAction() {
		
		$this->view->title = "Floris Thijs - Visual Effects Artist";
		
		return $this;
		
	}
	
}