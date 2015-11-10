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

use IPub;
use IPub\Security;
use IPubTests;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../lib/RolesModel.php';

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
		$config->addConfig(__DIR__ . '/../config/rolesModel.neon', $config::NONE);

		Security\DI\SecurityExtension::register($config);

		return $config->createContainer();
	}


	public function testFunctional()
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('ipubSecurity.permission') instanceof IPub\Security\Permission);
		Assert::true($dic->getService('ipubSecurity.checkers.annotation') instanceof IPub\Security\Access\AnnotationChecker);
		Assert::true($dic->getService('ipubSecurity.checkers.latte') instanceof IPub\Security\Access\LatteChecker);
		Assert::true($dic->getService('ipubSecurity.checkers.link') instanceof IPub\Security\Access\LinkChecker);
		Assert::true($dic->getService('models.roles') instanceof IPubTests\RolesModel);
	}
}


\run(new ExtensionTest());
