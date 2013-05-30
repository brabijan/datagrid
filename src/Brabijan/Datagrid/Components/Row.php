<?php

namespace Brabijan\Datagrid\Components;

use Nette;
use QOP;

class Row extends Nette\Application\UI\Control
{

	/** @var array */
	private $columns;

	/** @var mixed */
	public $data;

	/** @var Nette\Callback */
	private $templateHelpersCallback;

	/** @var Nette\Callback */
	private $templateRowCallback;



	/**
	 * @param array $data
	 */
	public function __construct($data)
	{
		parent::__construct();
		$this->data = $data;
	}



	public function attached($presenter)
	{
		parent::attached($presenter);

		/** @var $renderer \Brabijan\Datagrid\Renderer */
		$renderer = $this->lookup('Brabijan\Datagrid\Renderer');
		$this->columns = $renderer->getColumns();
		$this->templateHelpersCallback = $renderer->getTemplateHelpersCallback();
		$this->templateRowCallback = $renderer->getTemplateRowCallback();
	}



	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		return $template;
	}



	/**
	 *
	 */
	public function render()
	{
		$this->template->setFile(__DIR__ . "/row.latte");
		if ($this->templateHelpersCallback) {
			$this->templateHelpersCallback->invokeArgs(array($this->template));
		}

		/** @var $renderer \Brabijan\Datagrid\Renderer */
		$renderer = $this->lookup('Brabijan\Datagrid\Renderer');
		$this->template->columns = array_keys($renderer->getColumns());
		$this->template->render();
	}



	protected function createComponentColumn()
	{
		$data = $this->data;
		$templateRowCallback = $this->templateRowCallback;

		return new Nette\Application\UI\Multiplier(function ($columnId) use ($data, $templateRowCallback) {
			return new Column((int) $columnId, $data, $templateRowCallback);
		});
	}

}