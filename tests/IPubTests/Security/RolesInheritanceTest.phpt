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
require __DIR__ . '/../lib/RolesModel.php';


class RolesInheritanceTest extends Tester\TestCase
{
	/** @var Security\Models\IRolesModel */
	private $rolesModel;

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
		$config->addConfig(__DIR__ . '/../config/models.neon', $config::NONE);

		Security\DI\SecurityExtension::register($config);

		return $config->createContainer();
	}


	public function setUp()
	{
		parent::setUp();

		$container = $this->createContainer();

		$this->rolesModel = $container->getByType('IPub\Security\Models\IRolesModel');
		$this->permission = $container->getService('ipubSecurity.permission');
	}


	public function testRolesModelHierarchy()
	{
		$roles = $this->rolesModel->findAll();

		Assert::null($roles['guest']->getParent(), '"guest" does not have parent');
		Assert::count(1, $roles['guest']->getChildren(), '"guest" does have one children');

		Assert::equal($roles['guest'], $roles['authenticated']->getParent(), '"guest" is parent of "authenticated"');
		Assert::count(0, $roles['authenticated']->getChildren(), '"authenticated" does not have any children');

		Assert::null($roles['administrator']->getParent(), '"administrator" does not have parent');
		Assert::count(0, $roles['administrator']->getChildren(), '"administrator" does not have any children');

		Assert::null($roles['employee']->getParent(), '"employee" does not have parent');
		Assert::count(2, $roles['employee']->getChildren(), '"employee" does have 2 children');

		Assert::equal($roles['employee'], $roles['sales']->getParent(), '"employee" is parent role of "sales"');
		Assert::count(0, $roles['sales']->getChildren(), '"sales" does not have any children');

		Assert::equal($roles['employee'], $roles['engineer']->getParent(), '"employee" is parent role of "engineer"');
		Assert::count(1, $roles['engineer']->getChildren(), '"engineer" does have 1 children');

		Assert::equal($roles['engineer'], $roles['backend-engineer']->getParent(), '"engineer" is parent role of "backend-engineer"');
		Assert::count(0, $roles['backend-engineer']->getChildren(), '"backend-engineer" does not have any children');
	}


	public function testPermissionRolesHierarchy()
	{
		Assert::equal($this->permission->getRoleParents('guest'), array(), '"guest" does not have parent');

		Assert::equal($this->permission->getRoleParents('authenticated'), array('guest'), '"authenticated" does not have parent');

		Assert::equal($this->permission->getRoleParents('administrator'), array(), '"administrator" does not have parent');

		Assert::equal($this->permission->getRoleParents('employee'), array(), '"employee" does not have parent');

		Assert::equal($this->permission->getRoleParents('sales'), array('employee'), '"employee" is parent role of "sales"');

		Assert::equal($this->permission->getRoleParents('engineer'), array('employee'), '"employee" is parent role of "engineer"');

		Assert::equal($this->permission->getRoleParents('backend-engineer'), array('engineer'),
			'"engineer" is parent role of "backend-engineer"');
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
