<?php

class Base_Controller extends Base_Functions {
	
	public $postVars = array();
	public $getVars = array();
	
	public function _init($getVars, $postVars) {
		$this->view = new stdClass();
		$this->getVars = $getVars;
		$this->postVars = $postVars;
	}
	
	public function getVars() {
		if (count($this->getVars) > 0) {
			return $this->getVars;
		}
	 	else {
	 		return false;
	 	}
	}
	
	public function getVar($var, $default = false) {
		if (!array_key_exists($var, $this->getVars)) {
			return $default;
		}
		else {
			return $this->getVars[$var];
		}
	}
	
	public function postVars() {
		if (count($this->postVars) > 0) {
			return $this->postVars;
		}
	 	else {
	 		return false;
	 	}
	}
	
	public function postVar($var, $default = false) {
		if (!array_key_exists($var, $this->postVars)) {
			return $default;
		}
		else {
			return $this->postVars[$var];
		}
	}
	
}