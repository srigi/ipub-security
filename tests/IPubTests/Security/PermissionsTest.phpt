<?php
/**
 * Test: IPub\Security\Permissions
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Permissions!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		14.01.15
 */

namespace IPubTests\Security;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Security;
use IPub\Security\Entities;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../lib/RolesModel.php';

class PermissionsTest extends Tester\TestCase
{
	/**
	 * @var Security\Models\IRolesModel
	 */
	private $rolesModel;

	/**
	 * @var Security\Permission
	 */
	private $permission;


	/**
	 * @return array[]|array
	 */
	public function dataValidPermissions()
	{
		return [
			['firstResourceName:firstPrivilegeName', [
				'title' => 'This is example title',
				'description' => 'This is example description'
			]],
			[(new Security\Entities\Permission('secondResourceName', 'secondPrivilegeName', [
				'title' => 'This is second example title',
				'description' => 'This is second example description'
			])), NULL],
			[
				[
					'resource' => 'thirdResourceName',
					'privilege' => 'thirdPrivilegeName'
				],
				NULL,
			]
		];
	}


	/**
	 * @return array[]|array
	 */
	public function dataInvalidPermissions()
	{
		return [
			['wrongStringVersion', [
				'title' => 'This is example title',
				'description' => 'This is example description'
			]],
			[
				[
					'resource' => 'thirdResourceName',
					'wrongKey' => 'thirdPrivilegeName'
				]
			]
		];
	}


	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addConfig(__DIR__ . '/../config/rolesModel.neon', $config::NONE);

		Security\DI\PermissionsExtension::register($config);

		return $config->createContainer();
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
	}


	/**
	 * @dataProvider dataValidPermissions
	 *
	 * @param mixed|NULL $permission
	 * @param array|NULL $details
	 */
	public function testRegisteringPermissions($permission, array $details = NULL)
	{
		$this->permission->addPermission($permission, $details);
		$registeredPermissions = $this->permission->getPermissions();

		if (is_string($permission)) {
			list($resource, $privilege) = explode(Security\Permission::DELIMITER, $permission);
		}
		else if (is_array($permission)) {
			$resource = $permission['resource'];
			$privilege = $permission['privilege'];
		}
		else if ($permission instanceof Entities\IPermission) {
			$resource = $permission->getResource();
			$privilege = $permission->getPrivilege();
		}

		Assert::noError(function() use ($registeredPermissions, $resource, $privilege) {
			$searchPermission = $resource . Security\Permission::DELIMITER . $privilege;

			foreach ($registeredPermissions as $key => $registeredPermission) {
				if ($key === $searchPermission) {
					return;
				}
			}

			throw new Tester\AssertException("Unable to find permission in registered permissions", $searchPermission, NULL);
		});

		Assert::contains($resource, $this->permission->getResources(), 'Resource registered in ACL system');
	}


	/**
	 * @dataProvider dataInvalidPermissions
	 *
	 * @param mixed|NULL $permission
	 * @param array|NULL $details
	 *
	 * @throws IPub\Security\Exceptions\InvalidArgumentException
	 */
	public function testRegisteringInvalidPermissions($permission, array $details = NULL)
	{
		$this->permission->addPermission($permission, $details);
	}


	public function testRolePermissions()
	{
		foreach ($this->dataValidPermissions() as $permissionPair) {
			list($permission, $detail) = $permissionPair;
			$this->permission->addPermission($permission, $detail);
		}

		Assert::true($this->permission->isAllowed('guest', 'firstResourceName', 'firstPrivilegeName'));
		Assert::false($this->permission->isAllowed('guest', 'secondResourceName', 'secondPrivilegeName'));
		Assert::false($this->permission->isAllowed('guest', 'thirdResourceName', 'thirdPrivilegeName'));

		Assert::true($this->permission->isAllowed('authenticated', 'firstResourceName', 'firstPrivilegeName'));
		Assert::true($this->permission->isAllowed('authenticated', 'secondResourceName', 'secondPrivilegeName'));
		Assert::false($this->permission->isAllowed('authenticated', 'thirdResourceName', 'thirdPrivilegeName'));

		Assert::true($this->permission->isAllowed('administrator', 'firstResourceName', 'firstPrivilegeName'));
		Assert::true($this->permission->isAllowed('administrator', 'secondResourceName', 'secondPrivilegeName'));
		Assert::true($this->permission->isAllowed('administrator', 'thirdResourceName', 'thirdPrivilegeName'));
	}
}


\run(new PermissionsTest());
