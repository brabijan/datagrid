<?php

namespace Brabijan\Datagrid;

use Nette;

class Control extends Nette\Application\UI\Control {

	/** @var Renderer */
	private $renderer;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var string */
	private $templateFile;

	public function __construct(Renderer $renderer) {
		parent::__construct();
		$this->renderer = $renderer;
		$this->templateFile = __DIR__ . '/control.latte';
	}

	public function setTranslator(Nette\Localization\ITranslator $translator) {
		$this->translator = $translator;
	}

	public function setTemplateFile($filename) {
		$this->templateFile = $filename;
	}

	public function render() {
		$this->template->setFile($this->templateFile);
		$rows = array();
		$primaryKey = $this->renderer->getRowPrimaryKey();
		foreach($this->renderer->getData() as $row) {
			$rows[] = $this["row_" . $row[$primaryKey]] = new Components\Row( $this->renderer->getColumns(), $row );
		}
		$this->template->rows = $rows;
		$this->template->render();
	}

	public function createComponentHeader() {
		return new Components\Header( $this->renderer->getColumns() );
	}
}