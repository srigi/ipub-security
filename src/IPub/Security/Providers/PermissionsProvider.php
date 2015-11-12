<?php

namespace IPub\Security\Providers;

use Nette;
use IPub\Security\Entities;
use IPub\Security\Exceptions;


class PermissionsProvider extends Nette\Object implements IPermissionsProvider
{
	/** @var Entities\Resource[] */
	private $resources = [];

	/** @var Entities\Permission[] */
	private $permissions = [];


	/**
	 * @param string $resourceName
	 * @param Entities\IResource|NULL $parent
	 * @return Entities\Resource
	 */
	public function addResource($resourceName, Entities\IResource $parent = NULL)
	{
		if (array_key_exists($resourceName, $this->resources)) {
			throw new Exceptions\InvalidStateException("Resource \"$resourceName\" has been already added");
		}

		$resource = new Entities\Resource($resourceName);
		if ($parent) $resource->setParent($parent);

		$this->resources[$resourceName] = $resource;

		return $resource;
	}


	/**
	 * @param $resourceName
	 * @return Entities\Resource
	 */
	public function getResource($resourceName)
	{
		if (!array_key_exists($resourceName, $this->resources)) {
			throw new Exceptions\InvalidStateException("Resource \"$resourceName\" is not in the registry");
		}

		return $this->resources[$resourceName];
	}


	/**
	 * @param Entities\IResource|NULL $resource
	 * @param string|NULL $privilege
	 * @param callable|NULL $assertion
	 * @return Entities\Permission
	 */
	public function addPermission(Entities\IResource $resource = NULL, $privilege = NULL, callable $assertion = NULL)
	{
		$permission = new Entities\Permission($resource, $privilege, $assertion);
		$permissionStr = (string) $permission;

		if (array_key_exists($permissionStr, $this->permissions)) {
			throw new Exceptions\InvalidStateException("Permission \"$permissionStr\" is already registered");
		}

		$this->permissions[$permissionStr] = $permission;

		return $permission;
	}


	/**
	 * @return Entities\Permission[]
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}


	/**
	 * @return Entities\Resource[]
	 */
	public function getResources()
	{
		return $this->resources;
	}
}
