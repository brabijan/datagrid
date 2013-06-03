<?php

namespace Brabijan\Datagrid;

use Nette;

class DummyIterator extends Nette\Object
{

	/** @var int */
	private $position;

	/** @var int */
	private $length;



	public function __construct($key, $data)
	{
		$this->position = array_search($key, array_keys( (array) $data)) + 1;
		$this->length = count($data);
	}



	public function isFirst()
	{
		return $this->position === 1;
	}



	public function isLast()
	{
		return ($this->position % $this->length) === 0;
	}



	public function isOdd()
	{
		return $this->position % 2 === 1;
	}



	public function isEven()
	{
		return $this->position % 2 === 1;
	}



	public function getCounter()
	{
		return $this->position;
	}

}