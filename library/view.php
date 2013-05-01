<?php

class view {
	
	public $arr = array();
	public $file;
	
	public function __construct($file) {
		
		$this->file = $file;
		
	}
	
	protected function get_sub_views($obj) {
		
		foreach ($obj as $varname => $varvalue) {
			
			if ($varvalue instanceof view) {
				$obj->arr[$varname] = $varvalue->get_sub_views($varvalue);
			} else {
				$obj->arr[$varname] = $varvalue;
			}
			
		}
		extract($obj->arr);
		
		ob_start();
			if (file_exists(dirname(__FILE__).'/../application/view/scripts/'.$obj->file)) {
				
				if (!isset($this->title)) $this->title = "";
				if (!isset($this->scripts)) $this->scripts = "";
				if (!isset($this->styles)) $this->styles = "";
				include('/../application/view/scripts/'.$obj->file);
				
			}
			else {
				
				throw new Exception('The view file '.dirname(__FILE__).'/../application/view/scripts/'.$obj->file.' is not available');
				
			}
		$this->content = ob_get_clean();
		
		ob_start();
			if (file_exists(dirname(__FILE__).'/../application/view/scripts/'.$obj->file)) {
				
				include('/../application/view/layout/index.phtml');
				
			}
			else {
				
				throw new Exception('The view file '.dirname(__FILE__).'/../application/view/scripts/'.$obj->file.' is not available');
				
			}
		$html = ob_get_clean();
		
		return $html;
		
	}
	
	public function render() {
		
		echo self::get_sub_views($this);
		
	}
	
}