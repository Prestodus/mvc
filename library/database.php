<?php

class database extends config {
	
	private $dbh;
	
	public function __construct($database) {
		var_dump($this->loadConfig);
		
		try {
			$this->dbh = new PDO('mysql:host='.$database['host'].';dbname='.$database['name'], $database['username'], $database['password']);
		}
		catch(PDOException $e) {
			return $e;
		}
		return $this->dbh;
	}
	
}rzqr
