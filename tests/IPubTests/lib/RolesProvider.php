<?php

namespace IPubTests;

use IPub\Security;
use IPub\Security\Entities;


class RolesProvider extends Security\Providers\RolesProvider
{
	/**
	 * Roles are defined like this:
	 *
	 *  IRole::ROLE_ADMINISTRATOR (administrator)
	 *  IRole::ROLE_ANONYMOUS (guest)
	 *  └ IRole::ROLE_AUTHENTICATED (authenticated)
	 *  employee
	 *  ├ sales
	 *  └ engineer
	 *    └ backend-engineer
	 *  auditor
	 *
	 * Here are also role permission assigned, see the code.
	 *
	 * @param Security\Providers\IPermissionsProvider $permissionsProvider
	 */
	public function __construct(Security\Providers\IPermissionsProvider $permissionsProvider)
	{
		$permissions = $permissionsProvider->getPermissions();

		$this->addRole(Entities\IRole::ROLE_ADMINISTRATOR);
		$this->addRole(Entities\IRole::ROLE_ANONYMOUS, NULL, $permissions['intranet:access']);
		$this->addRole(Entities\IRole::ROLE_AUTHENTICATED, $this->getRole(Entities\IRole::ROLE_ANONYMOUS), [
			$permissions['climatisation:'],
		]);

		$this->addRole('employee', NULL, [
			$permissions['climatisation:'],
			$permissions['documents:'],
			$permissions['intranet:access'],
		]);
		$this->addRole('sales', $this->getRole('employee'), [
			$permissions['salesModule:'],
		]);
		$this->addRole('engineer', $this->getRole('employee'), [
			$permissions['servers:access'],
		]);
		$this->addRole('backend-engineer', $this->getRole('engineer'), [
			$permissions['servers:restart'],
			$permissions['databaseFarm:restart'],
		]);

		$this->addRole('auditor', NULL, [
			$permissions['intranet:access'],
		]);
	}
}
