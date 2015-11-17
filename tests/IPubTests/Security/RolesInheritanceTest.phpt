<?php
/**
 * Test: IPub\Security\Permissions
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Igor Hlina http://www.srigi.sk
 * @package		iPub:Security!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		23.07.15
 */

namespace IPubTests\Security;

use Nette;

use Tester;
use Tester\Assert;

use IPub\Security;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../lib/PermissionsProvider.php';
require __DIR__ . '/../lib/RolesProvider.php';


class RolesInheritanceTest extends Tester\TestCase
{
	/** @var Security\Providers\IRolesProvider */
	private $rolesProvider;

	/** @var Security\Permission */
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

		$container = $this->createContainer();

		$this->rolesProvider = $container->getByType('IPub\Security\Providers\IRolesProvider');
		$this->permission = $container->getService('ipubSecurity.permission');
	}


	public function testRolesProviderHierarchy()
	{
		$roles = $this->rolesProvider->findAll();

		Assert::null($roles['administrator']->getParents(), '"administrator" does not have parents');
		Assert::count(0, $roles['administrator']->getChildren(), '"administrator" does not have any children');

		Assert::null($roles['guest']->getParents(), '"guest" does not have parents');
		Assert::count(1, $roles['guest']->getChildren(), '"guest" does have one children');

		Assert::count(1, $roles['authenticated']->getParents(), '"authenticated" have one parent');
		Assert::equal($roles['guest'], $roles['authenticated']->getParents()[0], 'parent role of "authenticated" is "guest"');
		Assert::count(0, $roles['authenticated']->getChildren(), '"authenticated" does not have any children');

		Assert::null($roles['employee']->getParents(), '"employee" does not have parents');
		Assert::count(2, $roles['employee']->getChildren(), '"employee" does have 2 children');

		Assert::count(1, $roles['sales']->getParents(), '"sales" have one parent');
		Assert::equal($roles['employee'], $roles['sales']->getParents()[0], 'parent role of "sales" is "employee"');
		Assert::count(0, $roles['sales']->getChildren(), '"sales" does not have any children');

		Assert::count(1, $roles['engineer']->getParents(), '"engineer" have one parent');
		Assert::equal($roles['employee'], $roles['engineer']->getParents()[0], 'parent role of "engineer" is "employee"');
		Assert::count(1, $roles['engineer']->getChildren(), '"engineer" does have 1 children');

		Assert::count(1, $roles['backend-engineer']->getParents(), '"backend-engineer" have one parent');
		Assert::equal($roles['engineer'], $roles['backend-engineer']->getParents()[0], 'parent role of "backend-engineer" is "engineer"');
		Assert::count(0, $roles['backend-engineer']->getChildren(), '"backend-engineer" does not have any children');
	}


	public function testPermissionRolesHierarchy()
	{
		Assert::equal($this->permission->getRoleParents('administrator'), array(), '"administrator" does not have parents');

		Assert::equal($this->permission->getRoleParents('guest'), array(), '"guest" does not have parents');

		Assert::equal($this->permission->getRoleParents('authenticated'), array('guest'), '"guest" is the only parent role of "authenticated"');

		Assert::equal($this->permission->getRoleParents('employee'), array(), '"employee" does not have parents');

		Assert::equal($this->permission->getRoleParents('sales'), array('employee'), '"employee" is the only parent role of "sales"');

		Assert::equal($this->permission->getRoleParents('engineer'), array('employee'), '"employee" is the only parent role of "engineer"');

		Assert::equal($this->permission->getRoleParents('backend-engineer'), array('engineer'),
			'"engineer" is the only parent role of "backend-engineer"');
		Assert::true($this->permission->roleInheritsFrom('backend-engineer', 'engineer'),
			'"backend-engineer" inherits from "engineer"');
		Assert::true($this->permission->roleInheritsFrom('backend-engineer', 'employee'),
			'"backend-engineer" inherits also from "employee"');
	}


	public function testPermissionInheritance()
	{
		Assert::true($this->permission->isAllowed('employee', 'climatisation'),
				'"employee" is allowed "climatisation:"');
		Assert::true($this->permission->isAllowed('engineer', 'climatisation'),
				'"engineer" is also allowed "climatisation:" because it inherits from "employee"');
	}
}


\run(new RolesInheritanceTest());
