<?php
/**
 * Test: IPub\Permissions\Permissions
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Igor Hlina http://www.srigi.sk
 * @package		iPublikuj:Permissions!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		23.07.15
 */

namespace IPubTests\Permissions;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Permissions;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../lib/RolesModel.php';

class InheritanceTest extends Tester\TestCase
{
	/**
	 * @var Permissions\Models\IRolesModel
	 */
	private $rolesModel;

	/**
	 * @var Permissions\Security\Permission
	 */
	private $permission;


	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addConfig(__DIR__ . '/../config/rolesModel.neon', $config::NONE);

		Permissions\DI\PermissionsExtension::register($config);

		return $config->createContainer();
	}


	/**
	 * @return string[]
	 */
	public function dataValidPermissions()
	{
		return [
			'firstResourceName:firstPrivilegeName',
			'secondResourceName:secondPrivilegeName',
			'thirdResourceName:thirdPrivilegeName',
		];
	}


	/**
	 * Set up
	 */
	public function setUp()
	{
		parent::setUp();

		$dic = $this->createContainer();

		$this->rolesModel = $dic->getService('models.roles');
		$this->permission = $dic->getService('permissions.permissions');

		foreach ($this->dataValidPermissions() as $permission) {
			$this->permission->addPermission($permission);
		}
	}


	public function testRolesModelHierarchy()
	{
		$allRoles = $this->rolesModel->findAll();

		Assert::null($allRoles[0]->getParent(), '"guest" does not have parent');
		Assert::count(0, $allRoles[0]->getChildren(), '"guest" does not have any children');

		Assert::null($allRoles[1]->getParent(), '"authenticated" does not have parent');
		Assert::count(0, $allRoles[1]->getChildren(), '"authenticated" does not have any children');

		Assert::null($allRoles[2]->getParent(), '"administrator" does not have parent');
		Assert::count(0, $allRoles[2]->getChildren(), '"administrator" does not have any children');

		Assert::null($allRoles[3]->getParent(), '"employee" does not have parent');
		Assert::count(2, $allRoles[3]->getChildren(), '"employee" does have 2 children');

		Assert::equal($allRoles[3], $allRoles[4]->getParent(), '"employee" is parent role of "sales"');
		Assert::count(0, $allRoles[4]->getChildren(), '"sales" does not have any children');

		Assert::equal($allRoles[3], $allRoles[5]->getParent(), '"employee" is parent role of "engineer"');
		Assert::count(1, $allRoles[5]->getChildren(), '"engineer" does have 1 children');

		Assert::equal($allRoles[5], $allRoles[6]->getParent(), '"engineer" is parent role of "backend-engineer"');
		Assert::count(0, $allRoles[6]->getChildren(), '"backend-engineer" does not have any children');
	}


	public function testPermissionRolesHierarchy()
	{
		Assert::equal($this->permission->getRoleParents('guest'), array(), '"guest" does not have parent');

		Assert::equal($this->permission->getRoleParents('authenticated'), array(), '"authenticated" does not have parent');

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


	public function testPermissionChild()
	{
		Assert::true($this->permission->isAllowed('sales', 'firstResourceName', 'firstPrivilegeName'),
				'"sales" is allowed "firstResourceName:firstPrivilegeName"');
	}


	public function testPermissionInheritance()
	{
		Assert::true($this->permission->isAllowed('employee', 'firstResourceName', 'firstPrivilegeName'),
				'"employee" is allowed "firstResourceName:firstPrivilegeName"');
		Assert::true($this->permission->isAllowed('engineer', 'firstResourceName', 'firstPrivilegeName'),
				'"engineer" is also allowed "firstResourceName:firstPrivilegeName"');
	}
}

\run(new InheritanceTest());
