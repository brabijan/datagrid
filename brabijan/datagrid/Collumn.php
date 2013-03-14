<?php

namespace Brabijan\Datagrid;

use QOP,
	Nette;

class Collumn extends Nette\Object {

	/** @var string */
	private $name;

	/** @var string */
	public $mappedParameter;

	/** @var string */
	public $content;

	/** @var bool */
	private $hideTitle = false;

	/**
	 * @param string $name name of
	 * @param array  $mappedParameter array of mapped parameters
	 * @param string $format latte string template
	 */
	public function __construct($name, $mappedParameter = null, $format = null) {
		if(!is_array($mappedParameter)) {
			$mappedParameter = array($mappedParameter);
		}
		$this->name = $name;
		$this->mappedParameter = $mappedParameter;
		$this->content = $format;
	}

	/**
	 * @return $this provide fluent interface
	 */
	public function hideTitle() {
		$this->hideTitle = true;
		return $this;
	}

	/**
	 * @return string collumn name
	 */
	public function getName($ingoreHidden = false) {
		$title = $this->name;
		if(!$ingoreHidden and $this->hideTitle) {
			$title = "";
		}
		return $title;
	}

	/**
	 * @param string $content latte string template
	 */
	public function setContent($content) {
		$this->content = $content;
	}

}