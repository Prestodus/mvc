<?php

class newView {
	
	public $view;
	public $action;
	
	public function __construct($object, $action) {
		
		$this->view = $object->view;
		$this->action = $action;
		
	}
	
	public function __render($part = null) {
		
		$viewfolder = dirname(__FILE__).'/../application/view/';
		if ($part == null) {
		
			if (!isset($this->view->title)) $this->view->title = '';
			if (!isset($this->view->styles)) $this->view->styles = '';
			if (!isset($this->view->scripts)) $this->view->scripts = '';
			
			include_once($viewfolder.'layout/index.phtml');
			
		} elseif ($part == 'content') {
					
			if (file_exists($viewfolder.'scripts/'.$this->action.'.phtml')) {
				
				include_once($viewfolder.'scripts/'.$this->action.'.phtml');
				
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
	
}