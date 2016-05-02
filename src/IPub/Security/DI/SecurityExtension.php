<?php
/**
 * SecurityExtension.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPub:Security!
 * @subpackage	DI
 * @since		5.0
 *
 * @date		10.10.14
 */

namespace IPub\Security\DI;

use Nette;
use Nette\DI;
use Nette\PhpGenerator as Code;


class SecurityExtension extends DI\CompilerExtension
{
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$def = $builder->addDefinition($this->prefix('permission'))
			->setClass('IPub\Security\Permission');
		if (array_key_exists('redirectUrl', $config)) {
			$def->addSetup('setRedirectUrl', [$config['redirectUrl']]);
		}

		$builder->addDefinition($this->prefix('checkers.annotation'))
			->setClass('IPub\Security\Access\AnnotationChecker');

		$builder->addDefinition($this->prefix('checkers.latte'))
			->setClass('IPub\Security\Access\LatteChecker');

		$builder->addDefinition($this->prefix('checkers.link'))
			->setClass('IPub\Security\Access\LinkChecker');
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Install latte macros
		$latteFactory = $builder->getDefinition($builder->getByType('\Nette\Bridges\ApplicationLatte\ILatteFactory') ?: 'nette.latteFactory');
		$latteFactory->addSetup('IPub\Security\Latte\Macros::install(?->getCompiler())', array('@self'));
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, $extensionName = 'ipubSecurity')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new self());
		};
	}
}
