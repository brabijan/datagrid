<?php

namespace Brabijan\Datagrid;

use Nette;

class Column extends Nette\Object
{

	/** @var string */
	private $name;

	/** @var string */
	public $mappedParameter;

	/** @var string */
	public $content;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var bool */
	private $hideTitle = FALSE;



	/**
	 * @param string $name name of
	 * @param array $mappedParameter array of mapped parameters
	 * @param string $format latte string template
	 */
	public function __construct($name, $mappedParameter = NULL, $format = NULL)
	{
		if (!is_array($mappedParameter)) {
			$mappedParameter = array($mappedParameter);
		}
		$this->name = $name;
		$this->mappedParameter = $mappedParameter;
		$this->content = $format;
	}



	public function setTranslator(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}



	/**
	 * @return $this provide fluent interface
	 */
	public function hideTitle()
	{
		$this->hideTitle = TRUE;

		return $this;
	}



	/**
	 * @param bool $ingoreHidden
	 * @return string
	 */
	public function getName($ingoreHidden = FALSE)
	{
		$title = $this->name;
		if (!$ingoreHidden and $this->hideTitle) {
			$title = "";
		}
		if ($this->translator instanceof Nette\Localization\ITranslator and !$ingoreHidden) {
			$title = $this->translator->translate($title);
		}

		return $title;
	}



	/**
	 * @param string $content latte string template
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

}