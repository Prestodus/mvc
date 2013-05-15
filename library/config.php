<?php

class config {
	
	public $loadConfig;
	
	public function setConfig() {
		$this->loadConfig = parse_ini_file('../application/configs/config.ini', true);
		return $this->loadConfig;
	}
	
}