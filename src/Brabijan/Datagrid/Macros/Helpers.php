<?php

namespace Brabijan\Datagrid\Macros;

use Nette;

class Helpers extends Nette\Object {

	public static function getCustomTemplateFile($customTemplate, Nette\Templating\FileTemplate $currentTemplate) {
		if (substr($customTemplate, 0, 1) !== '/' && substr($customTemplate, 1, 1) !== ':') {
			$customTemplate = dirname($currentTemplate->getFile()) . '/' . $customTemplate;
		}
		$tpl = clone $currentTemplate;
		$tpl->setFile($customTemplate);
		return $tpl;
	}

}