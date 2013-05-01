<?php

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
			
			die('Class '.$class.' could not be loaded by the autoloader');
			
		}
		
	}

}