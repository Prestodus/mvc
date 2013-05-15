<?php

class dbCon extends config {
	
	public function connect() {
		$config = $this->setConfig();
		
		try {
			$this->dbh = new PDO('mysql:host='.$config['db']['host'].';dbname='.$config['db']['name'], $config['db']['username'], $config['db']['password']);
		} catch(PDOException $e) {
			$this->dbh = $e;
		}
		
		return $this->dbh;
	}
	
}