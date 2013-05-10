<?php

class view extends Base_Functions {
	
	public $view;
	public $action;
	public $controller;
	
	public function __construct($object, $controller, $action) {
		
		$this->view = $object->view;
		$this->controller = $controller;
		$this->action = $action;
		
	}
	
	public function __render($part = null) {
		
		$viewfolder = ROOT_PATH.'/application/view/';
		
		if ($part == null) {
		
			if (!isset($this->view->title)) $this->view->title = '';
			if (!isset($this->view->styles)) $this->view->styles = '';
			if (!isset($this->view->scripts)) $this->view->scripts = '';
			
			foreach ($this->view as $key => $value) {
				$this->$key = $value;
			}
			
			include_once($viewfolder.'layout/index.phtml');
			
		} elseif ($part == 'content') {
			
			$this->action = str_replace('_', '/', $this->action);
			if (file_exists($viewfolder.'scripts/'.$this->controller.'/'.$this->action.'.phtml')) {
				
				include_once($viewfolder.'scripts/'.$this->controller.'/'.$this->action.'.phtml');
				
			}
			else {
			
				echo 'De view \''.$this->action.'.phtml\' bestaat niet.';
				
			}
		
		}
		else {
			
			if (file_exists($viewfolder.''.$part.'.phtml')) {
			
				include_once($viewfolder.''.$part.'.phtml');
				
			} else {
			
				echo 'De view \''.$part.'.phtml\' bestaat niet.';
				
			}
		
		}
		
		return $this;
		
	}
	
	public function thisPage($controller, $action) {
		if ($this->controller == $controller && $this->action == $action) return true;
		else return false;
	}
	
}