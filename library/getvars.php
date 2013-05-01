<?php

$_request = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
foreach ($_request as $key => $value) {
	
	if ($value == '') unset($_request[$key]);
	
}
$_request = array_values($_request);
$_getVars = array();

if (!count($_request)) {
	
	$_page = strtolower('index');
	$_action = strtolower('index');
	
} elseif (count($_request) < 2) {
	
	$_page = strtolower($_request[0]);
	$_action = strtolower('index');
	
} else {
	
	$_page = strtolower($_request[0]);
	$_action = strtolower($_request[1]);
	for($i=2; $i<count($_request); $i=$i+2) {
		
		$_getVars[$_request[$i]] = strtolower((isset($_request[$i+1])?$_request[$i+1]:''));
		
	}
	
}