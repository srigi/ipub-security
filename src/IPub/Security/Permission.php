<?php
/**
 * Permission.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPub:Security!
 * @subpackage	Security
 * @since		5.0
 *
 * @date		10.10.14
 */

namespace IPub\Security;

use IPub\Security\Entities;
use IPub\Security\Providers;
use Nette\Security as NS;


class Permission extends NS\Permission implements NS\IAuthorizator
{
	/**
	 * @param Providers\IPermissionsProvider $permissionsProvider
	 * @param Providers\IRolesProvider $rolesProvider
	 */
	public function __construct(Providers\IPermissionsProvider $permissionsProvider, Providers\IRolesProvider $rolesProvider)
	{
		$resources = $permissionsProvider->getResources();  /** @var Entities\IResource[] $resources */
		$roles = $rolesProvider->findAll();                 /** @var Entities\IRole[] $roles */

		// Register resources into Nette\Security\Permission
		foreach ($resources as $resource) {
			$resourceParent = $resource->getParent();
			$this->addResource($resource->getName(), ($resourceParent) ? $resourceParent->getName() : NULL);
		}

		// Register roles into Nette\Security\Permission & setup role permissions
		foreach ($roles as $role) {
			$roleParent = $role->getParent();
			$this->addRole($role->getName(), ($roleParent) ? $roleParent->getName() : NULL);

			// Allow all privileges for administrator
			if ($role->isAdministrator()) {
				$this->allow($role->getName());
			} else {
				$rolePermissions = $role->getPermissions();
				foreach ($rolePermissions as $permission) {  /** @var Entities\IPermission $permission */
					$resource = $permission->getResource();
					$resource = ($resource) ? $resource->getName() : NS\IAuthorizator::ALL;
					$this->allow($role->getName(), $resource, $permission->getPrivilege(), $permission->getAssertion());
				}
			}
		}
	}
}
