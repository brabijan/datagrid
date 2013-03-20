<?php

namespace Brabijan\Datagrid;

use Nette,
	Nette\Application\UI\Form,
	QOP,
	Kdyby;

class Renderer extends Nette\Application\UI\Control {

	const PAGINATION_NONE = "__pagination_none_";
	const PAGINATION_TOP = "__pagination_top_";
	const PAGINATION_BOTTOM = "__pagination_bottom_";
	const PAGINATION_BOTH = "__pagination_both_";

	/** @var mixed array|Nette\Database\Table\Selection */
	private $data = array();

	/** @var array */
	private $columns = array();

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var string */
	private $rowPrimaryKey = "id";

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

	/** @var bool */
	private $filterManualRender = false;

	/** @persistent */
	public $filter = array();

	/**
	 * @param $name
	 * @param $mappedParameter
	 * @param null $format
	 * @return Column
	 */
	public function addColumn($name, $mappedParameter, $format = null) {
		$column = new Column( $name, $mappedParameter, $format );
		if($this->translator instanceof Nette\Localization\ITranslator) {
			$column->setTranslator($this->translator);
		}
		$this->columns[$name] = $column;

		return $column;
	}

	/**
	 * @param Nette\Localization\ITranslator $translator
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator) {
		$this->translator = $translator;
	}

	/**
	 * @return array
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * @param $data mixed array|Nette\Database\Table\Selection
	 */
	public function setData($data) {
		$this->data = $data;
	}

	/**
	 * @return mixed array|Nette\Database\Table\Selection
	 */
	public function getData() {
		$data = $this->data;
		if(!empty($this->filter)) {
			$data = $this->filterCallback->invokeArgs(array($data, $this->filter));
		}
		if($this->isPaginatorEnabled()) {
			$this->paginator->setItemCount(count($data));
			$data = $this->paginatorCallback->invokeArgs(array($data, $this->paginator->getLength(), $this->paginator->getOffset()));
		}
		return $data;
	}

	/**
	 * @param $key string
	 */
	public function setRowPrimaryKey($key) {
		$this->rowPrimaryKey = $key;
	}

	/**
	 * @return string
	 */
	public function getRowPrimaryKey() {
		return $this->rowPrimaryKey;
	}

	/**
	 * @param string $columnName column name
	 */
	public function removeColumn($columnName) {
		unset($this->columns[$columnName]);
	}

	/**
	 * @param $columnName
	 * @return Column
	 */
	public function getColumn($columnName) {
		return $this->columns[$columnName];
	}

	/**
	 * @param string $column column name
	 * @param string $where first|last|before|after
	 * @param string $after column name
	 * @throws \Nette\InvalidStateException
	 */
	public function move($column, $where, $after = null) {
		if($where == "first") {
			$columns = array();
			$columns[$column] = $this->columns[$column];
			foreach($this->columns as $columnName => $tmpcolumn) {
				if($columnName!=$column) {
					$columns[$columnName] = $tmpcolumn;
				}
			}
			$this->columns = $columns;
			return;
		}
		elseif($where == "last") {
			$columns = array();
			foreach($this->columns as $columnName => $tmpcolumn) {
				if($columnName!=$column) {
					$columns[$columnName] = $tmpcolumn;
				}
			}
			$columns[$column] = $this->columns[$column];
			$this->columns = $columns;
			return;
		}
		elseif($where == "before") {
			if($after == null) {
				throw new Nette\InvalidStateException("Some parameter missing");
			}
			$columns = array();
			foreach($this->columns as $columnName => $tmpcolumn) {
				if($columnName==$after) {
					$columns[$column] = $this->columns[$column];
				}
				if($columnName!=$column) {
					$columns[$columnName] = $tmpcolumn;
				}
			}
			$this->columns = $columns;
			return;
		}
		elseif($where == "after") {
			if($after == null) {
				throw new Nette\InvalidStateException("Some parameter missing");
			}
			$columns = array();
			foreach($this->columns as $columnName => $tmpcolumn) {
				if($columnName!=$column) {
					$columns[$columnName] = $tmpcolumn;
				}
				if($columnName==$after) {
					$columns[$column] = $this->columns[$column];
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
	public function getRenderer() {
		return $this;
	}


	/************************************************ paginator *******************************************************/

	/**
	 * @param $itemsPerPage
	 */
	public function enablePaginator($itemsPerPage) {
		$this->paginator = new Nette\Utils\Paginator();
		$this->paginator->setItemsPerPage($itemsPerPage);
	}

	/**
	 * @return bool
	 */
	public function isPaginatorEnabled() {
		return $this->paginator instanceof Nette\Utils\Paginator;
	}

	/**
	 * @param callable $paginatorCallback
	 */
	public function setPaginatorCallback($paginatorCallback) {
		$this->paginatorCallback = new Nette\Callback($paginatorCallback);
	}

	/**
	 * @param $position
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setPaginationPositions($position) {
		if($position !== self::PAGINATION_NONE and
			$position !== self::PAGINATION_BOTH and
				$position !== self::PAGINATION_BOTTOM and
					$position !== self::PAGINATION_TOP) {
			throw new Nette\InvalidArgumentException;
		}
		$this->paginationPositions = $position;
	}

	/**
	 * @return string
	 */
	public function getPaginationPositions() {
		return $this->paginationPositions;
	}

	/**
	 * @return Nette\Utils\Paginator
	 * @throws \Nette\InvalidStateException
	 */
	public function getPaginator() {
		if(!($this->paginator instanceof Nette\Utils\Paginator)) {
			throw new Nette\InvalidStateException("Enable paginator first using enablePaginator()");
		}
		return $this->paginator;
	}


	/************************************************** filter ********************************************************/

	public function setFilterFormFactory($callback) {
		$this->filterFormFactory = new Nette\Callback($callback);
	}

	public function setFilterCallback($callback) {
		$this->filterCallback = new Nette\Callback($callback);
	}

	public function setFilterManualRender() {
		$this->filterManualRender = true;
	}

	protected function createComponentFilterForm() {
		$form = new Form;
		$form->addContainer("filter");
		$this->filterFormFactory->invokeArgs(array($form["filter"]));
		$form->addSubmit("send", "Filter!");
		$form->onSuccess[] = $this->filterFormSubmitted;
		if($this->translator instanceof Nette\Localization\ITranslator) {
			$form->setTranslator($this->translator);
		}
		$form->setRenderer(new Kdyby\BootstrapFormRenderer\BootstrapRenderer());
		return $form;
	}

	public function filterFormSubmitted(Form $form) {
		$this->filter = $form->values["filter"];
	}


	/************************************************** control *******************************************************/

	public function render() {
		$this->template->setFile(__DIR__ . '/control.latte');
		$rows = array();
		$primaryKey = $this->getRowPrimaryKey();
		foreach($this->getData() as $row) {
			$rows[] = $this["row_" . $row[$primaryKey]] = new Components\Row( $this->getColumns(), $row );
		}

		if($this->isPaginatorEnabled()) {
			$this->template->paginationPosition = $this->paginationPositions;
			$this->template->paginator = $this->paginator;
		}
		else {
			$this->template->paginationPosition = Renderer::PAGINATION_NONE;
		}
		$this->template->renderFilter = !$this->filterManualRender;
		$this->template->rows = $rows;
		$this->template->render();
	}

	public function handleSetPage($page) {
		$this->paginator->setPage($page);
		if($this->presenter->isAjax()) {
			$this->invalidateControl("datagrid");
		}
	}

	public function createComponentHeader() {
		return new Components\Header( $this->getColumns() );
	}

}