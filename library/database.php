<?php

class database {
	
	private $dbh;
	
	public function __construct($database) {
		try {
			$this->dbh = new PDO('mysql:host='.$database['host'].';dbname='.$database['name'], $database['username'], $database['password']);
		}
		catch(PDOException $e) {
			return $e;
		}
		return $this->dbh;
	}
	
}