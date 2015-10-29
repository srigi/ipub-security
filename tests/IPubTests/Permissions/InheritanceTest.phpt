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

		Assert::null($allRoles[0]->getParent(), 'Guest does not have parent');
		Assert::count(0, $allRoles[0]->getChildren(), 'Guest does not have any children');

		Assert::null($allRoles[1]->getParent(), 'Authenticated does not have parent');
		Assert::count(0, $allRoles[1]->getChildren(), 'Authenticated does not have any children');

		Assert::null($allRoles[2]->getParent(), 'Administrator does not have parent');
		Assert::count(0, $allRoles[2]->getChildren(), 'Administrator does not have any children');

		Assert::null($allRoles[3]->getParent(), 'user-defined-role does not have parent');
		Assert::count(2, $allRoles[3]->getChildren(), 'user-defined-role does have 2 children');

		Assert::equal($allRoles[3], $allRoles[4]->getParent(), 'user-defined-role is parent role of user-defined-child-role');
		Assert::count(0, $allRoles[4]->getChildren(), 'user-defined-child-role does not have any children');

		Assert::equal($allRoles[3], $allRoles[5]->getParent(), 'user-defined-role is parent role of user-defined-inherited-role');
		Assert::count(1, $allRoles[5]->getChildren(), 'user-defined-inherited-role does have 1 children');

		Assert::equal($allRoles[5], $allRoles[6]->getParent(), 'user-defined-inherited-role is parent role of user-defined-inherited-inherited-role');
		Assert::count(0, $allRoles[6]->getChildren(), 'user-defined-inherited-inherited-role does not have any children');
	}


	public function testPermissionRolesHierarchy()
	{
		Assert::equal($this->permission->getRoleParents('guest'), array(), 'Guest does not have parent');

		Assert::equal($this->permission->getRoleParents('authenticated'), array(), 'Authenticated does not have parent');

		Assert::equal($this->permission->getRoleParents('administrator'), array(), 'Administrator does not have parent');

		Assert::equal($this->permission->getRoleParents('user-defined-role'), array(), 'user-defined-role does not have parent');

		Assert::equal($this->permission->getRoleParents('user-defined-child-role'), array('user-defined-role'),
			'user-defined-role is parent role of user-defined-child-role');

		Assert::equal($this->permission->getRoleParents('user-defined-inherited-role'), array('user-defined-role'),
			'user-defined-role is parent role of user-defined-inherited-role');

		Assert::equal($this->permission->getRoleParents('user-defined-inherited-inherited-role'), array('user-defined-inherited-role'),
			'user-defined-inherited-role is parent role of user-defined-inherited-inherited-role');
		Assert::true($this->permission->roleInheritsFrom('user-defined-inherited-inherited-role', 'user-defined-inherited-role'),
			'user-defined-inherited-inherited-role inherits from user-defined-inherited-role');
		Assert::true($this->permission->roleInheritsFrom('user-defined-inherited-inherited-role', 'user-defined-role'),
			'user-defined-inherited-inherited-role inherits also from user-defined-role');
	}


	public function testPermissionChild()
	{
		Assert::true($this->permission->isAllowed('user-defined-child-role', 'firstResourceName', 'firstPrivilegeName'));
	}


	public function testPermissionInheritance()
	{
		Assert::true($this->permission->isAllowed('user-defined-role', 'firstResourceName', 'firstPrivilegeName'));
		Assert::true($this->permission->isAllowed('user-defined-inherited-role', 'firstResourceName', 'firstPrivilegeName'));
	}
}

\run(new InheritanceTest());
