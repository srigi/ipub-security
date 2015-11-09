<?php
/**
 * Test: IPub\Permissions\Extension
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Permissions!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		13.01.15
 */

namespace IPubTests;

use IPub\Permissions;
use IPub\Permissions\Entities;

class RolesModel implements Permissions\Models\IRolesModel
{
	/**
	 * Roles & permissions are defined like this:
	 *
	 *  guest(Entities\IRole::ROLE_ANONYMOUS)              ---  firstResourceName:firstPrivilegeName
	 *  authenticated(Entities\IRole::ROLE_AUTHENTICATED)  ---  firstResourceName:firstPrivilegeName, secondResourceName:secondPrivilegeName
	 *  administrator(Entities\IRole::ROLE_ADMINISTRATOR)
	 *
	 *  Employee                                           ---  firstResourceName:firstPrivilegeName, secondResourceName:secondPrivilegeName
	 *  ├ Sales
	 *  └ Engineer                                         ---  thirdResourceName:thirdPrivilegeName
	 *    └ Backend-engineer
	 *
	 * @return Entities\IRole[]
	 */
	public function findAll()
	{
		$guest = (new Permissions\Entities\Role(Entities\IRole::ROLE_ANONYMOUS))
			->setPriority(0)
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
			]);

		$authenticated = (new Permissions\Entities\Role(Entities\IRole::ROLE_AUTHENTICATED))
			->setPriority(0)
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
				'secondResourceName:secondPrivilegeName',
			]);

		$administrator = (new Permissions\Entities\Role(Entities\IRole::ROLE_ADMINISTRATOR))
			->setPriority(0);

		$employee = (new Permissions\Entities\Role('employee'))
			->setPriority(0)
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
				'secondResourceName:secondPrivilegeName',
			]);

		$sales = (new Permissions\Entities\Role('sales'))
			->setPriority(0)
			->setParent($employee);

		$engineer = (new Permissions\Entities\Role('engineer'))
			->setPriority(0)
			->setParent($employee)
			->setPermissions([
				'thirdResourceName:thirdPrivilegeName',
			]);

		$backendEngineer = (new Permissions\Entities\Role('backend-engineer'))
			->setPriority(0)
			->setParent($engineer);

		return [
			$guest,
			$authenticated,
			$administrator,
			$employee,
			$sales,
			$engineer,
			$backendEngineer,
		];
	}
}
