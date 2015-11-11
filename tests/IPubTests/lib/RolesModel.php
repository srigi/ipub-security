<?php

namespace IPubTests;

use Nette;
use IPub\Security;
use IPub\Security\Entities;
use IPubTests\Exceptions;


class RolesModel extends Nette\Object implements Security\Models\IRolesModel
{
	/** @var Entities\IRole[] */
	private $roles = [];


	/**
	 * Roles & permissions are defined like this:
	 *
	 *  IRole::ROLE_ANONYMOUS (guest)
	 *  IRole::ROLE_AUTHENTICATED (authenticated)
	 *  IRole::ROLE_ADMINISTRATOR (administrator)
	 *  employee
	 *  ├ sales
	 *  └ engineer
	 *    └ backend-engineer
	 *
	 * Here are also role permission assigned, see the code.
	 *
	 * @param Security\Models\IPermissionsProvider $permissionsProvider
	 */
	public function __construct(Security\Models\IPermissionsProvider $permissionsProvider)
	{
		$permissions = $permissionsProvider->getPermissions();

		$this->addRole(Entities\IRole::ROLE_ANONYMOUS, NULL, $permissions['intranet:access']);
		$this->addRole(Entities\IRole::ROLE_AUTHENTICATED, $this->getRole(Entities\IRole::ROLE_ANONYMOUS), [
			$permissions['climatisation:'],
		]);
		$this->addRole(Entities\IRole::ROLE_ADMINISTRATOR);

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
	}


	/**
	 * @param string $roleName
	 * @param Entities\IRole|NULL $parent
	 * @param Entities\IPermission|Entities\IPermission[]|NULL $permissions
	 * @return Entities\Role
	 */
	public function addRole($roleName, Entities\IRole $parent = NULL, $permissions = NULL)
	{
		if (array_key_exists($roleName, $this->roles)) {
			throw new Exceptions\InvalidStateException("Role \"$roleName\" is already registered");
		}

		if ($permissions instanceof Entities\IPermission) {
			$permissions = [$permissions];
		}

		$role = new Entities\Role($roleName);
		if ($parent) $role->setParent($parent);
		if ($permissions) $role->setPermissions($permissions);

		$this->roles[$roleName] = $role;

		return $role;
	}


	/**
	 * @param string $roleName
	 * @return Entities\IRole
	 */
	public function getRole($roleName)
	{
		if (!array_key_exists($roleName, $this->roles)) {
			throw new Exceptions\InvalidArgumentException("Role \"$roleName\" is not registered");
		}

		return $this->roles[$roleName];
	}


	/**
	 * @return Entities\IRole[]
	 */
	public function findAll()
	{
		return $this->roles;
	}
}
