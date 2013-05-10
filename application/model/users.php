<?php

class users {
	
	public $naam;
	public $voornaam;
	public $postcode;
	public $plaats;
	public $straat;
	public $huisnummer;
	public $telefoonnummer;

	public function setNaam($naam) {
		$this->naam = $naam;
		return $this;
	}
	public function getNaam() {
		return $this->naam;
	}

	public function setVoornaam($voornaam) {
		$this->voornaam = $voornaam;
		return $this;
	}
	public function getVoornaam() {
		return $this->voornaam;
	}

	public function setPostcode($postcode) {
		$this->postcode = (int) $postcode;
		return $this;
	}
	public function getPostcode() {
		return $this->postcode;
	}

	public function setPlaats($plaats) {
		$this->plaats = $plaats;
		return $this;
	}
	public function getPlaats() {
		return $this->plaats;
	}

	public function setStraat($straat) {
		$this->straat = $straat;
		return $this;
	}
	public function getStraat() {
		return $this->straat;
	}

	public function setHuisnummer($huisnummer) {
		$this->huisnummer = (int) $huisnummer;
		return $this;
	}
	public function getHuisnummer() {
		return $this->huisnummer;
	}

	public function setTelefoonnummer($telefoonnummer) {
		$this->telefoonnummer = (int) $telefoonnummer;
		return $this;
	}
	public function getTelefoonnummer() {
		return $this->telefoonnummer;
	}
	
}