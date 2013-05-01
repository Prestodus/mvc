<?php

function dump($var, $exit = null) {
	
	$var_dump = '';
	if(isset($var)) {
		
		echo "<pre>";
		ob_start();
			var_dump($var);
		$dump = ob_get_clean();
		highlight_string("<?php\n\n$dump\n?>");
		echo "</pre>";
		
	} else {
		
		echo "Variable doesn't exist!";
		
	}
	if ($exit != null) exit();
	
}

session_start();
error_reporting(E_ALL);

include('../application/bootstrap.php');