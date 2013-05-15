<?php

include_once('../library/config.php');

class bootstrap extends config {
	
	public $config;
	public $getVars;
	public $postVars;
	
	public function __construct() {
		//$this->config = parse_ini_file('../application/configs/config.ini', true);
		$this->config = $this->setConfig();
		$this->getVars()->postVars();
	}

	public function getVars() {
		$request = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
		foreach ($request as $key => $value) {
			if ($value == '') unset($request[$key]);
		}
		$request = array_values($request);
		$getVars = array();
		
		if (!count($request)) {
			$controller = strtolower('index');
			$action = strtolower('index');
		}
		elseif (count($request) < 2) {
			$controller = strtolower($request[0]);
			$action = strtolower('index');
		}
		else {
			$controller = strtolower($request[0]);
			$action = strtolower($request[1]);
			for($i=2; $i<count($request); $i+=2) {
				$getVars[$request[$i]] = strtolower((isset($request[$i+1])?$request[$i+1]:''));
			}
		}
		$getVars['controller'] = $controller;
		$getVars['action'] = $action;
		$this->getVars = $getVars;
		
		return $this;
	}
	
	public function postVars() {
		foreach ($_POST as $key => $value) {
			$this->postVars[$key] = $value;
		}
		
		if (!is_array($this->postVars)) {
			$this->postVars = array();
		}
		
		return $this;
	}
	
	public function getWebPath() {
		return $this->config['website']['path'];
	}
	
	public function getConfig() {
		return $this->config;
	}
	
	public function initializePage() {
		include_once('../library/autoload.php');
		include_once('../library/base/Base_Functions.php');
		include_once('../library/base/Base_Controller.php');
		
		if (is_readable(ROOT_PATH.'/application/controller/'.$this->getVars['controller'].'Controller.php')) {
			include('../application/controller/'.$this->getVars['controller'].'Controller.php');
			$error = false;
		}
		else {
			$error = true;
		}
		
		if (!$error) {
			$controller = $this->getVars['controller'].'Controller';
			$action = $this->getVars['action'].'Action';
			
			try {
				if (class_exists($controller)) {
					$dispatch = new $controller;
					$dispatch->_init($this->getVars, $this->postVars);
				}
				else {
					throw new Exception("An error has ocurred. The controller has not been found.");
				}
				if (method_exists($dispatch, $action)) {
					$object = call_user_func_array(array($dispatch, $action), $this->getVars);
					$view = new view($object, $this->getVars['controller'], $this->getVars['action']);
					$view->__render();
				}
				else {
					throw new Exception("An error has ocurred. The action has not been found.");
				}
			}
			catch (Exception $e) {
				$error = true;
			}
		}
		if ($error) {
			include('../application/controller/error404Controller.php');
			$dispatch = new error404Controller();
			$dispatch->_init($this->getVars, $this->postVars);
			$object = call_user_func_array(array($dispatch, 'error404Action'), $this->getVars);
			$view = new view($object, 'errors', 'error404');
			$view->__render();
		}
	}
	
}