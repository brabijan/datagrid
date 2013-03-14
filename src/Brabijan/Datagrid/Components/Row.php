<?php

namespace Brabijan\Datagrid\Components;

use Nette,
	QOP;

class Row extends Nette\Application\UI\Control {

	/** @var array */
	private $collumns;

	/** @var mixed */
	private $data;

	/**
	 * @param array $collumns
	 * @param array $data
	 */
	public function __construct($collumns, $data) {
		parent::__construct();
		$this->collumns = $collumns;
		$this->data = $data;
	}

	/**
	 *
	 */
	public function render() {
		$this->template->setFile(__DIR__ . "/row.latte");
		$collumns = array();
		$data = $this->data;
		foreach($this->collumns as $collumn) {
			if($collumn->content == null) {
				$content = "";
				foreach($collumn->mappedParameter as $parameter) {
					$content .= '{$' . $parameter . '} ';
				}
			}
			else {
				$content = $collumn->content;
			}
			$template = $this->createTemplate('Nette\Templating\Template');
			$template->{'_control'} = $this->presenter;
			foreach($collumn->mappedParameter as $parameter) {
				$template->{$parameter} = $data[$parameter];
			}
			$template->setSource($content);
			$collumns[] = (string) $template;
		}
		$this->template->collumns = $collumns;
		$this->template->render();
	}
}