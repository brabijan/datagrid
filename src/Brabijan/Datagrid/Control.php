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

		if($this->renderer->isPaginatorEnabled()) {
			$this->template->paginationPosition = $this->renderer->paginationPositions;
			$this->template->paginator = $this->renderer->paginator;
		}
		else {
			$this->template->paginationPosition = Renderer::PAGINATION_NONE;
		}
		$this->template->rows = $rows;
		$this->template->render();
	}

	public function handleSetPage($page) {
		$this->renderer->paginator->setPage($page);
		if($this->presenter->isAjax()) {
			$this->invalidateControl("datagrid");
		}
	}

	public function createComponentHeader() {
		return new Components\Header( $this->renderer->getColumns() );
	}
}