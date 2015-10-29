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
	 *  administrator(Entities\IRole::ROLE_ADMINISTRATOR)  ---  firstResourceName:firstPrivilegeName, secondResourceName:secondPrivilegeName, thirdResourceName:thirdPrivilegeName
	 *
	 *  user-defined-role                                  ---  firstResourceName:firstPrivilegeName, secondResourceName:secondPrivilegeName
	 *  ├ user-defined-child-role
	 *  └ user-defined-inherited-role                      ---  thirdResourceName:thirdPrivilegeName
	 *    └ user-defined-inherited-inherited-role
	 *
	 * @return Entities\IRole[]
	 */
	public function findAll()
	{
		$guest = (new Permissions\Entities\Role)
			->setKeyName(Entities\IRole::ROLE_ANONYMOUS)
			->setName('Guest')
			->setPriority(0)
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
			]);

		$authenticated = (new Permissions\Entities\Role)
			->setKeyName(Entities\IRole::ROLE_AUTHENTICATED)
			->setName('Authenticated')
			->setPriority(0)
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
				'secondResourceName:secondPrivilegeName',
			]);

		$administrator = (new Permissions\Entities\Role)
			->setKeyName(Entities\IRole::ROLE_ADMINISTRATOR)
			->setName('Administrator')
			->setPriority(0)
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
				'secondResourceName:secondPrivilegeName',
				'thirdResourceName:thirdPrivilegeName',
			]);

		$custom = (new Permissions\Entities\Role)
			->setKeyName('user-defined-role')
			->setName('Registered in custom role')
			->setPriority(0)
			->setPermissions([
				'firstResourceName:firstPrivilegeName',
				'secondResourceName:secondPrivilegeName',
			]);

		$customChild = (new Permissions\Entities\Role)
			->setKeyName('user-defined-child-role')
			->setName('Registered in custom role as children of another role')
			->setPriority(0)
			->setParent($custom)
			->setPermissions([
			]);

		$customInherited = (new Permissions\Entities\Role)
			->setKeyName('user-defined-inherited-role')
			->setName('Registered in custom role inheriting another role')
			->setPriority(0)
			->setParent($custom)
			->setPermissions([
				'thirdResourceName:thirdPrivilegeName',
			]);

		$customInheritedInherited = (new Permissions\Entities\Role)
			->setKeyName('user-defined-inherited-inherited-role')
			->setName('Registered in custom role inheriting another role')
			->setPriority(0)
			->setParent($customInherited)
			->setPermissions([
			]);

		return [
			$guest,
			$authenticated,
			$administrator,
			$custom,
			$customChild,
			$customInherited,
			$customInheritedInherited,
		];
	}
}
