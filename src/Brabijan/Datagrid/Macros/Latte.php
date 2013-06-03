<?php

namespace Brabijan\Datagrid\Macros;

use Nette;
use Nette\Latte\Compiler;
use Nette\Latte\MacroNode;
use Nette\Latte\PhpWriter;

/**
 * @author Jan Brabec <brabijan@gmail.com>
 */
class Latte extends Nette\Latte\Macros\MacroSet
{

	/** @var string */
	private $templateVariable;



	/**
	 * @param \Nette\Latte\Compiler $compiler
	 *
	 * @return Latte|\Nette\Latte\Macros\MacroSet
	 */
	public static function install(Compiler $compiler)
	{
		/** @var $me Latte */
		$me = new static($compiler);
		$me->addMacro('datagrid', array($me, 'macroDatagrid'), array($me, 'macroDatagridEnd'));
		$me->addMacro('template', array($me, 'macroTemplate'), array($me, 'macroTemplateEnd'));

		return $me;
	}



	public function macroDatagrid(MacroNode $node, PhpWriter $writer)
	{
		$pair = $node->tokenizer->fetchWord();
		if ($pair === FALSE) {
			throw new Nette\Latte\CompileException("Missing datagrid name in {datagrid}");
		}
		$pair = explode(':', $pair, 2);
		$name = $writer->formatWord($pair[0]);

		return ($name[0] === '$' ? "if (is_object($name)) \$_ctrl = $name; else " : '')
			. '$_datagrid = $_control->getComponent(' . $name . '); '
			. 'if ($_datagrid instanceof Nette\Application\UI\IRenderable) $_datagrid->validateControl();';
	}



	public function macroDatagridEnd(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write("ob_start(); \$_datagrid->render(); echo %modify(ob_get_clean()); unset(\$_datagrid);");
	}



	public function macroTemplate(MacroNode $node, PhpWriter $writer)
	{
		$pair = $node->tokenizer->fetchWord();
		$var = explode(':', $pair, 2);
		$this->templateVariable = $writer->formatWord($var[0]);

		return $writer->write('/*');
	}



	public function macroTemplateEnd(MacroNode $node, PhpWriter $writer)
	{
		if ($node->content !== NULL) {
			$variable = $this->templateVariable;
			$this->templateVariable = NULL;

			return $writer->write('*/ $_datagrid->setRowVariable(\''. $variable.'\'); $_datagrid->setCustomRowTemplate(\'' . $writer->formatWord($node->content) . '\');');
		} else {
			$node->openingCode = '<?php ?>';
			$variable = $node->tokenizer->fetchWord();
			$customTemplate = trim($node->tokenizer->fetchWord(), "'\"");

			return $writer->write('$_datagrid->setRowVariable(\'' . $variable . '\'); $_datagrid->setCustomRowTemplate(Brabijan\Datagrid\Macros\Helpers::getCustomTemplateFile("'. $customTemplate.'", $_l->templates[%var]));', $this->getCompiler()->getTemplateId());
		}
	}

}