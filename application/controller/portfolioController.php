<?php

class portfolioController extends Base_Controller {

	public function indexAction() {

		$this->view->title = 'index title';

		return $this;

	}

}