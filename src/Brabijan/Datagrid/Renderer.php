<?php

namespace Brabijan\Datagrid;

use Nette,
	QOP;

class Renderer extends Nette\Object {

	/** @var mixed array|Nette\Database\Table\Selection */
	private $data = array();

	/** @var array */
	private $columns = array();

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var string */
	private $rowPrimaryKey;

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
		return $this->data;
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
	 * @return Control
	 */
	public function getRenderer() {
		return new Control( $this );
	}
}