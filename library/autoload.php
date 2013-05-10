<?php

function __autoload($class_name) {
	$inc = new autoload($class_name);
}

class autoload {
	
	public function __construct($class) {
		
		$paths = parse_ini_file('../application/configs/paths.ini', true);

		foreach($paths['include_paths'] as $path) {
			
			$class = str_replace('_', '/', $class);
			$file = $path.$class.'.php';
			if(file_exists($file)) {
				
				include $file;
				return;
				
			}
			
		}

		if(strstr($class, 'Controller')) {
			
			return false;
			
		}
		
	}

}