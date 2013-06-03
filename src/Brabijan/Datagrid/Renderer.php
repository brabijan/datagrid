<?php

namespace Brabijan\Datagrid;

use Nette;
use Nette\Application\UI\Form;

class Renderer extends Nette\Application\UI\Control
{

	const PAGINATION_NONE = "__pagination_none_";

	const PAGINATION_TOP = "__pagination_top_";

	const PAGINATION_BOTTOM = "__pagination_bottom_";

	const PAGINATION_BOTH = "__pagination_both_";

	/** @var mixed array|Nette\Database\Table\Selection */
	private $data = array();

	/** @var array */
	private $filteredData = array();

	/** @var Column[] */
	private $columns = array();

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var Nette\Utils\Paginator */
	private $paginator;

	/** @var string */
	private $paginationPositions = Renderer::PAGINATION_NONE;

	/** @var Nette\Callback */
	private $paginatorCallback;

	/** @var Nette\Callback */
	private $filterFormFactory;

	/** @var Nette\Callback */
	private $filterCallback;

	/** @var Nette\Callback */
	private $templateHelpersCallback;

	/** @var Nette\Callback */
	private $templateRowCallback;

	/** @var bool */
	private $filterManualRender = FALSE;

	/** @persistent */
	public $filter = array();

	/** @var string */
	private $customTemplate;

	/** @var string */
	private $customRowTemplate;

	/** @var string */
	private $customHeaderTemplate;



	/**
	 * @param $name
	 * @param $mappedParameter
	 * @param null $format
	 * @return Column
	 */
	public function addColumn($name, $mappedParameter, $format = NULL)
	{
		$column = new Column($name, $mappedParameter, $format);
		if ($this->translator instanceof Nette\Localization\ITranslator) {
			$column->setTranslator($this->translator);
		}
		$this->columns[] = $column;

		return $column;
	}



	/**
	 * @param Nette\Localization\ITranslator $translator
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}



	/**
	 * @return array
	 */
	public function getColumns()
	{
		return $this->columns;
	}



	/**
	 * @param $data mixed array|Nette\Database\Table\Selection
	 */
	public function setData($data)
	{
		$this->data = $data;
	}



	/**
	 * @return mixed array|Nette\Database\Table\Selection
	 */
	public function getData()
	{
		if (empty($this->filteredData)) {
			$data = $this->data;
			if (!empty($this->filter) and $this->filterCallback !== NULL) {
				$data = $this->filterCallback->invokeArgs(array($data, $this->filter));
			}
			if ($this->isPaginatorEnabled()) {
				$this->paginator->setItemCount(count($data));
				$data = $this->paginatorCallback->invokeArgs(array($data, $this->paginator->getLength(), $this->paginator->getOffset()));
			}
			foreach ($data as $row) {
				$this->filteredData[] = $row;
			}
		}

		return $this->filteredData;
	}



	private function getRow($rowPrimary)
	{
		if (empty($this->filteredData)) {
			$this->getData();
		}

		return $this->filteredData[$rowPrimary];
	}



	/**
	 * @param $key string
	 */
	public function setRowPrimaryKey($key)
	{
		trigger_error("Calling setRowPrimaryKey is deprecated", E_DEPRECATED);
	}



	/**
	 * @param string $columnName column name
	 */
	public function removeColumn($columnName)
	{
		if (is_string($columnName)) {
			$columnName = $this->getColumn($columnName, TRUE);
		}
		unset($this->columns[$columnName]);
	}



	/**
	 * @param $columnName
	 * @param bool $getKey
	 * @return Column|int
	 */
	public function getColumn($columnName, $getKey = FALSE)
	{
		if (is_int($columnName)) {
			return $this->columns[$columnName];
		} else {
			foreach ($this->columns as $key => $column) {
				if ($column->getName(TRUE) == $columnName) {
					return $getKey ? $key : $column;
				}
			}
		}
	}



	/**
	 * @param string $column column name
	 * @param string $where first|last|before|after
	 * @param string $after column name
	 * @throws \Nette\InvalidStateException
	 */
	public function move($column, $where, $after = NULL)
	{
		if ($where == "first") {
			$columns = array();
			$columns[] = $this->getColumn($column);
			foreach ($this->columns as $columnName => $tmpcolumn) {
				$columnName = $tmpcolumn->getName(TRUE);
				if ($columnName != $column) {
					$columns[$columnName] = $tmpcolumn;
				}
			}
			$this->columns = $columns;

			return;
		} elseif ($where == "last") {
			$columns = array();
			foreach ($this->columns as $tmpcolumn) {
				$columnName = $tmpcolumn->getName(TRUE);
				if ($columnName != $column) {
					$columns[] = $tmpcolumn;
				}
			}
			$columns[] = $this->getColumn($column);
			$this->columns = $columns;

			return;
		} elseif ($where == "before") {
			if ($after == NULL) {
				throw new Nette\InvalidStateException("Some parameter missing");
			}
			$columns = array();
			foreach ($this->columns as $tmpcolumn) {
				$columnName = $tmpcolumn->getName(TRUE);
				if ($columnName == $after) {
					$columns[] = $this->getColumn($column);
				}
				if ($columnName != $column) {
					$columns[] = $tmpcolumn;
				}
			}
			$this->columns = $columns;

			return;
		} elseif ($where == "after") {
			if ($after == NULL) {
				throw new Nette\InvalidStateException("Some parameter missing");
			}
			$columns = array();
			foreach ($this->columns as $tmpcolumn) {
				$columnName = $tmpcolumn->getName(TRUE);
				if ($columnName != $column) {
					$columns[] = $tmpcolumn;
				}
				if ($columnName == $after) {
					$columns[] = $this->getColumn($column);
				}
			}
			$this->columns = $columns;

			return;
		}
	}



	/**
	 * For back compatibility
	 *
	 * @return Control
	 */
	public function getRenderer()
	{
		return $this;
	}



	/************************************************ paginator *******************************************************/

	/**
	 * @param $itemsPerPage
	 */
	public function enablePaginator($itemsPerPage)
	{
		$this->paginator = new Nette\Utils\Paginator();
		$this->paginator->setItemsPerPage($itemsPerPage);
	}



	/**
	 * @return bool
	 */
	public function isPaginatorEnabled()
	{
		return $this->paginator instanceof Nette\Utils\Paginator;
	}



	/**
	 * @param callable $paginatorCallback
	 */
	public function setPaginatorCallback($paginatorCallback)
	{
		$this->paginatorCallback = new Nette\Callback($paginatorCallback);
	}



	public function getPaginatorCallback()
	{
		return $this->paginatorCallback;
	}



	/**
	 * @param $position
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setPaginationPositions($position)
	{
		if ($position !== self::PAGINATION_NONE and
			$position !== self::PAGINATION_BOTH and
				$position !== self::PAGINATION_BOTTOM and
					$position !== self::PAGINATION_TOP
		) {
			throw new Nette\InvalidArgumentException;
		}
		$this->paginationPositions = $position;
	}



	/**
	 * @return string
	 */
	public function getPaginationPositions()
	{
		return $this->paginationPositions;
	}



	/**
	 * @return Nette\Utils\Paginator
	 * @throws \Nette\InvalidStateException
	 */
	public function getPaginator()
	{
		if (!($this->paginator instanceof Nette\Utils\Paginator)) {
			throw new Nette\InvalidStateException("Enable paginator first using enablePaginator()");
		}

		return $this->paginator;
	}



	/************************************************** filter ********************************************************/

	public function setFilterFormFactory($callback)
	{
		$this->filterFormFactory = new Nette\Callback($callback);
	}



	public function setFilterCallback($callback)
	{
		$this->filterCallback = new Nette\Callback($callback);
	}



	public function getFilterCallback()
	{
		return $this->filterCallback;
	}



	public function setFilterManualRender()
	{
		$this->filterManualRender = TRUE;
	}



	protected function createComponentFilterForm()
	{
		$form = new Form;
		$form->addContainer("filter");
		if ($this->filterFormFactory) {
			$this->filterFormFactory->invokeArgs(array($form["filter"]));
		}
		$form["filter"]->setDefaults($this->filter);
		$form->addSubmit("send", "Filter!")->onClick[] = $this->filterFormSubmitted;
		$form->addSubmit("reset", "Reset")->onClick[] = function ($button) {
			foreach ($button->form["filter"]->getComponents() as $control) {
				$control->setValue(NULL);
			}
			$this->filter = array();
		};
		if ($this->translator instanceof Nette\Localization\ITranslator) {
			$form->setTranslator($this->translator);
		}
		$form->setRenderer(new Kdyby\BootstrapFormRenderer\BootstrapRenderer());

		return $form;
	}



	public function filterFormSubmitted(Nette\Forms\Controls\SubmitButton $button)
	{
		$form = $button->form;
		$this->filter = $form->values["filter"];
	}



	/************************************************** control *******************************************************/

	public function setCustomTemplate($file)
	{
		$this->customTemplate = $file;
	}



	public function getVisibility()
	{
		$data = $this->getData();

		return empty($data) ? FALSE : TRUE;
	}



	public function setCustomRowTemplate($file)
	{
		$this->customRowTemplate = $file;
	}



	public function setCustomHeaderTemplate($file)
	{
		$this->customHeaderTemplate = $file;
	}



	public function setTemplateHelpersCallback($templateHelpersCallback)
	{
		$this->templateHelpersCallback = new Nette\Callback($templateHelpersCallback);
	}



	public function getTemplateHelpersCallback()
	{
		return $this->templateHelpersCallback;
	}



	public function setTemplateRowCallback($templateRowCallback)
	{
		$this->templateRowCallback = new Nette\Callback($templateRowCallback);
	}



	public function getTemplateRowCallback()
	{
		return $this->templateRowCallback;
	}



	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		return $template;
	}



	public function render()
	{
		if ($this->customTemplate === NULL) {
			$this->template->setFile(__DIR__ . '/control.latte');
		} else {
			$this->template->setFile($this->customTemplate);
			$this->template->extend = __DIR__ . '/control.latte';
		}

		$this->template->rows = array_keys($this->getData());


		if ($this->isPaginatorEnabled()) {
			$this->template->paginationPosition = $this->paginationPositions;
			$this->template->paginator = $this->paginator;
		} else {
			$this->template->paginationPosition = Renderer::PAGINATION_NONE;
		}
		if ($this->templateHelpersCallback) {
			$this->templateHelpersCallback->invokeArgs(array($this->template));
		}
		$this->template->showFilter = ($this->filterManualRender == FALSE and $this->filterFormFactory !== NULL);

		$this->template->render();
	}



	public function handleSetPage($page)
	{
		$this->paginator->setPage($page);
		if ($this->presenter->isAjax()) {
			$this->invalidateControl("datagrid");
		}
	}



	public function createComponentHeader()
	{
		return new Components\Header($this->getColumns(), $this->customHeaderTemplate ? $this->customHeaderTemplate : NULL);
	}



	protected function createComponentRow()
	{
		$that = $this;

		return new Nette\Application\UI\Multiplier(function ($rowId) use ($that) {
			return new Components\Row($that->getRow($rowId));
		});
	}

}
