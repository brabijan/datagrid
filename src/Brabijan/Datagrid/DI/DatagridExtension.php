<?php

namespace Brabijan\Datagrid\DI;

use Nette;
use Nette\Config\Compiler;
use Nette\Config\Configurator;

/**
 * @author Jan Brabec <brabijan@gmail.com>
 */
class DatagridExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$engine = $builder->getDefinition('nette.latte');

		$install = 'Brabijan\Datagrid\Macros\Latte::install';
		$engine->addSetup($install . '(?->compiler)', array('@self'));
	}



	/**
	 * @param Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Configurator $config, $extensionName = 'datagridExtension')
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new DatagridExtension());
		};
	}

}