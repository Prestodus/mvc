<?php

$config = parse_ini_file('/../application/configs/config.ini', true);

define('WEB_PATH', $config['website']['path']);

require_once('/../library/getvars.php');

if (is_readable(dirname(__FILE__).'/controller/'.$_page.'Controller.php')) {
	include('/../application/controller/'.$_page.'Controller.php');
}
else {
	include('/../application/controller/404Controller.php');
}

$controller = $_page.'Controller';
$action = $_action.'Action';

try {
	if (class_exists($controller)) {
		$dispatch = new $controller;
		$dispatch->_init();
	}
	else {
		throw new Exception("controller bestaat niet");
	}
	if (method_exists($dispatch, $action)) {
		$object = call_user_func_array(array($dispatch, $action), $_getVars);
		$view = new newView($object, $_action);
		$view->__render();
	}
	else {
		throw new Exception("action bestaat niet");
	}
}
catch (Exception $e) {
	echo "<pre>".$e->getMessage()."</pre>";
}

function __autoload($class_name) {
	require_once('/../library/autoload.php');
	$inc = new autoload($class_name);
}