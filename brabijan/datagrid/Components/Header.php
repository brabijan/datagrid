<?php

namespace TwitterBootstrap\Datagrid;

use Nette,
	QOP;

class Header extends Nette\Application\UI\Control {

	/** @var array */
	private $collumns;

	public function __construct($collumns) {
		parent::__construct();
		$this->collumns = $collumns;
	}

	public function render() {
		$this->template->setFile(__DIR__ . "/header.latte");
		$this->template->collumns = $this->collumns;
		$this->template->render();
	}
}