<?php

namespace Brabijan\Datagrid\Components;

use Nette,
	QOP;

class Row extends Nette\Application\UI\Control {

	/** @var array */
	private $columns;

	/** @var mixed */
	public $data;

	/** @var Nette\Callback */
	private $templateHelpersCallback;

	/**
	 * @param array $columns
	 * @param array $data
	 * @param Nette\Callback $templateHelpersCallback
	 * @param string $customRowTemplate
	 */
	public function __construct($columns, $data, $templateHelpersCallback = null, $customRowTemplate = null) {
		parent::__construct();
		$this->columns = $columns;
		$this->data = $data;
		$this->templateHelpersCallback = $templateHelpersCallback;
	}

	/**
	 *
	 */
	public function render() {
		$this->template->setFile(__DIR__ . "/row.latte");
		$columns = array();
		$data = $this->data;
		foreach($this->columns as $column) {
			if($column->content == null) {
				$content = "";
				foreach($column->mappedParameter as $parameter) {
					$content .= '{$' . $parameter . '} ';
				}
			}
			else {
				$content = $column->content;
			}
			$template = $this->createTemplate('Nette\Templating\Template');
			$template->{'_control'} = $this->presenter;
			if($this->templateHelpersCallback)
				$this->templateHelpersCallback->invokeArgs(array($template));
			foreach($column->mappedParameter as $parameter) {
				$template->{$parameter} = $data[$parameter];
			}
			$template->setSource($content);
			$columns[] = (string) $template;
		}
		if($this->templateHelpersCallback)
			$this->templateHelpersCallback->invokeArgs(array($this->template));
		$this->template->columns = $columns;
		$this->template->render();
	}
}