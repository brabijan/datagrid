<?php

namespace Brabijan\Datagrid\Components;

use Nette;

class Collumn extends Nette\Application\UI\Control {

	/** @var array */
	public $data;

	/** @var \Brabijan\Datagrid\Column */
	private $column;

	/** @var integer */
	private $columnId;

	/** @var Nette\Callback */
	private $templateRowCallback;

	public function __construct($columnId, $data, $templateRowCallback) {
		$this->columnId = $columnId;
		$this->data = $data;
		$this->templateRowCallback = $templateRowCallback;
	}

	public function attached($presenter) {
		parent::attached($presenter);
		$this->column = $this->lookup('Brabijan\Datagrid\Renderer')->getColumn($this->columnId);
		if($this->templateRowCallback)
			$this->templateRowCallback->invokeArgs(array($this));
	}

	public function createTemplate($class = null) {
		$template = parent::createTemplate('Nette\Templating\Template');
		$template->registerHelperLoader('QOP\Template\Helpers::loader');
		return $template;
	}

	public function render() {
		$this->template->row = $this->data;
		foreach($this->column->mappedParameter as $parameter) {
			$this->template->{$parameter} = $this->data[$parameter];
		}
		if($this->column->content == null) {
			$content = "";
			foreach($this->column->mappedParameter as $parameter) {
				$content .= '{$' . $parameter . '} ';
			}
		}
		else {
			$content = $this->column->content;
		}
		$this->template->{'_control'} = $this->presenter;
		$this->template->setSource($content);
		$this->template->render();
	}

}