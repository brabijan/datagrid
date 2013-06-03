<?php

namespace Brabijan\Datagrid\Components;

use Nette;
use Brabijan;

class Row extends Nette\Application\UI\Control
{

	/** @var mixed */
	private $data;

	/** @var Brabijan\Datagrid\Renderer */
	private $renderer;

	/** @var Brabijan\Datagrid\DummyIterator */
	private $dummyIterator;



	/**
	 * @param array $data
	 */
	public function __construct($data, Brabijan\Datagrid\DummyIterator $dummyIterator)
	{
		parent::__construct();
		$this->data = $data;
		$this->dummyIterator = $dummyIterator;
	}



	public function attached($presenter)
	{
		parent::attached($presenter);
		$this->renderer = $this->lookup('Brabijan\Datagrid\Renderer');
	}



	public function createTemplate($class = NULL)
	{
		if ($this->renderer->getCustomRowTemplate() instanceof Nette\Templating\ITemplate) {
			$tpl = clone $this->renderer->getCustomRowTemplate();
		} else {
			$tpl = parent::createTemplate($class);
			$tpl->setFile(__DIR__ . "/row.latte");
		}

		if ($this->renderer->getTemplateHelpersCallback()) {
			$this->renderer->getTemplateHelpersCallback()->invokeArgs(array($tpl));
		}

		return $tpl;
	}



	/**
	 *
	 */
	public function render()
	{
		$this->template->{$this->renderer->getRowVariable()} = $this->data;
		$this->template->columns = array_keys($this->renderer->getColumns());
		$this->template->iterator = $this->dummyIterator;
		$this->template->render();
	}



	protected function createComponentColumn()
	{
		$data = $this->data;
		$templateRowCallback = $this->renderer->getTemplateRowCallback();

		return new Nette\Application\UI\Multiplier(function ($columnId) use ($data, $templateRowCallback) {
			return new Column((int) $columnId, $data, $templateRowCallback);
		});
	}

}



class AssetsManager extends Nette\Object
{

	/** @var string */
	private $jsPath;

	/** @var string */
	private $cssPath;

	/** @var array */
	private $js = array();

	/** @var array */
	private $css = array();

	/** @var bool */
	private $debugMode;



	public function __construct($debugMode, $jsPath, $cssPath)
	{
		$this->debugMode = $debugMode;
		$this->jsPath = $jsPath;
		$this->cssPath = $cssPath;
	}



	public function addCss($files)
	{
		if (!is_array($files)) {
			$files = array($files);
		}

		foreach ($files as $file) {
			$this->css[] = $file;
		}
	}



	public function addJs($files)
	{
		if (!is_array($files)) {
			$files = array($files);
		}

		foreach ($files as $file) {
			$this->js[] = $file;
		}
	}



	public function getCss()
	{
		$this->validateAssets();

		return $this->css;
	}



	public function getJs()
	{
		$this->validateAssets();

		return $this->js;
	}



	private function validateAssets()
	{
		if (!$this->debugMode) {
			return;
		}

		foreach ($this->css as $css) {
			if (file_exists($this->cssPath . $css)) {
				throw new Nette\FileNotFoundException("Style $css was not found");
			}
		}

		foreach ($this->js as $js) {
			if (file_exists($this->jsPath . $js)) {
				throw new Nette\FileNotFoundException("Style $js was not found");
			}
		}
	}
}