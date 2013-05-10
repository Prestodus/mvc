<?php

class Base_Functions {
	
	function createUrl($controller = 'index', $action = 'index', array $vars = array()) {
		
		$url = '/'.$controller.'/'.$action.'/';
		foreach ($vars as $key => $value) {
			$url .= $key.'/'.$value.'/';
		}
		return $url;
		
	}
	
	function redirect($controller = 'index', $action = 'index', array $vars = array()) {
		
		$url = '/'.$controller.'/'.$action.'/';
		foreach ($vars as $key => $value) {
			$url .= $key.'/'.$value.'/';
		}
		header('Location: '.$url);
		exit;
		
	}
	
}