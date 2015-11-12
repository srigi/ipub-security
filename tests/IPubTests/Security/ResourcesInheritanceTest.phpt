<?php

namespace IPubTests\Security;

use Nette;
use Tester;
use Tester\Assert;

use IPub;
use IPub\Security;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../lib/PermissionsProvider.php';
require __DIR__ . '/../lib/RolesProvider.php';


class ResourcesInheritanceTest extends Tester\TestCase
{
	/**
	 * @var Security\Permission
	 */
	private $permission;


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


	public function setUp()
	{
		parent::setUp();

		$dic = $this->createContainer();
		$this->permission = $dic->getService('ipubSecurity.permission');
	}


	public function testResourcePermissionsInheriting()
	{
		Assert::true($this->permission->isAllowed('auditor', 'intranet', 'access'),
			'auditor can access intranet');

		Assert::false($this->permission->isAllowed('auditor', 'intranet'),
			'auditor cannot do anything to intranet');

		Assert::true($this->permission->isAllowed('auditor', 'servers', 'access'),
			'auditor can access servers');

		Assert::true($this->permission->isAllowed('auditor', 'databaseFarm', 'access'),
			'auditor can access databaseFarm');

		Assert::false($this->permission->isAllowed('auditor', 'servers', 'restart'),
			'auditor cannot restart servers');

		Assert::false($this->permission->isAllowed('auditor', 'databaseFarm', 'restart'),
			'auditor cannot restart databaseFarm');
	}
}


\run(new ResourcesInheritanceTest());
