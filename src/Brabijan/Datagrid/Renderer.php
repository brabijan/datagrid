<?php

namespace Brabijan\Datagrid;

use Nette,
	QOP;

class Renderer extends Nette\Object {

	/** @var mixed array|Nette\Database\Table\Selection */
	private $data = array();

	/** @var array */
	private $collumns = array();

	/** @var string */
	private $rowPrimaryKey;

	/**
	 * @param      $name
	 * @param      $type
	 * @param null $mappedParameter
	 * @return Collumn
	 */
	public function addCollumn($name, $mappedParameter, $format = null) {
		$collumn = new Collumn( $name, $mappedParameter, $format );
		$this->collumns[$name] = $collumn;

		return $collumn;
	}

	/**
	 * @return array
	 */
	public function getCollumns() {
		return $this->collumns;
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
	 * @param string $collumnName collumn name
	 */
	public function removeCollumn($collumnName) {
		unset($this->collumns[$collumnName]);
	}

	/**
	 * @param $collumnName
	 * @return Collumn
	 */
	public function getCollumn($collumnName) {
		return $this->collumns[$collumnName];
	}

	/**
	 * @param string $collumn collumn name
	 * @param string $where first|last|before|after
	 * @param string $after collumn name
	 * @throws \Nette\InvalidStateException
	 */
	public function move($collumn, $where, $after = null) {
		if($where == "first") {
			$collumns = array();
			$collumns[$collumn] = $this->collumns[$collumn];
			foreach($this->collumns as $collumnName => $tmpCollumn) {
				if($collumnName!=$collumn) {
					$collumns[$collumnName] = $tmpCollumn;
				}
			}
			$this->collumns = $collumns;
			return;
		}
		elseif($where == "last") {
			$collumns = array();
			foreach($this->collumns as $collumnName => $tmpCollumn) {
				if($collumnName!=$collumn) {
					$collumns[$collumnName] = $tmpCollumn;
				}
			}
			$collumns[$collumn] = $this->collumns[$collumn];
			$this->collumns = $collumns;
			return;
		}
		elseif($where == "before") {
			if($after == null) {
				throw new Nette\InvalidStateException("Some parameter missing");
			}
			$collumns = array();
			foreach($this->collumns as $collumnName => $tmpCollumn) {
				if($collumnName==$after) {
					$collumns[$collumn] = $this->collumns[$collumn];
				}
				if($collumnName!=$collumn) {
					$collumns[$collumnName] = $tmpCollumn;
				}
			}
			$this->collumns = $collumns;
			return;
		}
		elseif($where == "after") {
			if($after == null) {
				throw new Nette\InvalidStateException("Some parameter missing");
			}
			$collumns = array();
			foreach($this->collumns as $collumnName => $tmpCollumn) {
				if($collumnName!=$collumn) {
					$collumns[$collumnName] = $tmpCollumn;
				}
				if($collumnName==$after) {
					$collumns[$collumn] = $this->collumns[$collumn];
				}
			}
			$this->collumns = $collumns;
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