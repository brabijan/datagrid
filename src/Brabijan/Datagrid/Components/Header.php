<?php

namespace Brabijan\Datagrid\Components;

use Nette,
	QOP;

class Header extends Nette\Application\UI\Control {

	/** @var array */
	private $columns;

	public function __construct($columns) {
		parent::__construct();
		$this->columns = $columns;
	}

	public function render() {
		$this->template->setFile(__DIR__ . "/header.latte");
		$this->template->columns = $this->columns;
		$this->template->render();
	}
}