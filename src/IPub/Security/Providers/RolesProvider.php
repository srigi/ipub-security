<?php

namespace IPub\Security\Providers;

use Nette;
use IPub\Security\Entities;
use IPub\Security\Exceptions;


class RolesProvider extends Nette\Object implements IRolesProvider
{
	/** @var Entities\Role[] */
	private $roles = [];


	/**
	 * @param $roleName
	 * @param Entities\IRole|NULL $parent
	 * @param null $permissions
	 * @return Entities\Role
	 */
	public function addRole($roleName, Entities\IRole $parent = NULL, $permissions = NULL)
	{
		if (array_key_exists($roleName, $this->roles)) {
			throw new Exceptions\InvalidStateException("Role \"$roleName\" has been already added");
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
	 * @return Entities\Role
	 */
	public function getRole($roleName)
	{
		if (!array_key_exists($roleName, $this->roles)) {
			throw new Exceptions\InvalidArgumentException("Role \"$roleName\" is not in the registry");
		}

		return $this->roles[$roleName];
	}


	/**
	 * @return Entities\Role[]
	 */
	public function findAll()
	{
		return $this->roles;
	}
}
