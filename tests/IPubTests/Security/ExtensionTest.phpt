<?php
/**
 * Test: IPub\Security\Extension
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPub:Security!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		13.01.15
 */

namespace IPubTests\Security;

use Nette;

use Tester;
use Tester\Assert;

use IPub\Security;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../lib/PermissionsProvider.php';
require __DIR__ . '/../lib/RolesProvider.php';


class ExtensionTest extends Tester\TestCase
{
	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addConfig(__DIR__ . '/../config/application.neon', $config::NONE);
		$config->addConfig(__DIR__ . '/../config/providers.neon', $config::NONE);

		Security\DI\SecurityExtension::register($config);

		return $config->createContainer();
	}


	public function testFunctional()
	{
		$container = $this->createContainer();

		Assert::true($container->getService('ipubSecurity.permission') instanceof Security\Permission);
		Assert::true($container->getService('ipubSecurity.checkers.annotation') instanceof Security\Access\AnnotationChecker);
		Assert::true($container->getService('ipubSecurity.checkers.latte') instanceof Security\Access\LatteChecker);
		Assert::true($container->getService('ipubSecurity.checkers.link') instanceof Security\Access\LinkChecker);
	}
}


\run(new ExtensionTest());
