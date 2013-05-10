<?php

class getsetController extends Base_Controller {
	
	public function indexAction() {
		
		$this->view->title = 'Create getters and setters';
		return $this;
		
	}
	
	public function outputAction() {
		
		$this->view->title = 'Create getters and setters - Output';
		if (isset($_POST['var'])) {
			
			$outputvars = '';
			$outputgetsets = '';
			foreach ($_POST['var'] as $key => $var) {
				$outputvars .= "\tpublic \$".strtolower($var).";\n";
				$outputgetsets .= "\n\tpublic function set".ucfirst(strtolower($var))."(\$".strtolower($var).") {\n\t\t\$this->".strtolower($var)." = ".(isset($_POST["int".$key])?"(int) ":"")."\$".strtolower($var).";\n\t\treturn \$this;\n\t}\n";
				$outputgetsets .= "\tpublic function get".ucfirst(strtolower($var))."() {\n\t\treturn \$this->".strtolower($var).";\n\t}\n";
			}
			$this->view->output = $outputvars."".$outputgetsets;
			
		}
		else {
		
			header('Location: /getset/index/');
		
		}
		
		return $this;
		
	}
	
}