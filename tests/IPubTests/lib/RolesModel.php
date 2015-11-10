<?php
/**
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

namespace IPubTests;

use IPub\Security;
use IPub\Security\Entities;

class RolesModel implements Security\Models\IRolesModel
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
		$guest = (new Security\Entities\Role(Entities\IRole::ROLE_ANONYMOUS))
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
			]);

		$authenticated = (new Security\Entities\Role(Entities\IRole::ROLE_AUTHENTICATED))
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
				'secondResourceName:secondPrivilegeName',
			]);

		$administrator = (new Security\Entities\Role(Entities\IRole::ROLE_ADMINISTRATOR));

		$employee = (new Security\Entities\Role('employee'))
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
				'secondResourceName:secondPrivilegeName',
			]);

		$sales = (new Security\Entities\Role('sales'))
			->setParent($employee);

		$engineer = (new Security\Entities\Role('engineer'))
			->setParent($employee)
			->setPermissions([
				'thirdResourceName:thirdPrivilegeName',
			]);

		$backendEngineer = (new Security\Entities\Role('backend-engineer'))
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
