<?php

namespace IPubTests;

use Nette;
use Nette\Security\IAuthorizator;
use IPub\Security;
use IPub\Security\Entities;
use IPubTests\Exceptions;


class PermissionsProvider extends Nette\Object implements Security\Models\IPermissionsProvider
{
	/** @var Entities\IResource[] */
	private $resources = [];

	/** @var Entities\Permission[] */
	private $permissions = [];


	/**
	 * Resources hierarchy is defined like this:
	 *
	 *  climatisation
	 *  documents
	 *  intranet
	 *  ├ salesModule
	 *  └ servers
	 *    └ databaseFarm
	 */
	public function __construct()
	{
		$climatisation = $this->addResource('climatisation');
		$this->addPermission($climatisation, IAuthorizator::ALL);

		$documents = $this->addResource('documents');
		$this->addPermission($documents, IAuthorizator::ALL, function($acl, $role, $resource, $privilege) {
		});
		$this->addPermission($documents, 'access', function($acl, $role, $resource, $privilege) {
		});
		$this->addPermission($documents, 'create');
		$this->addPermission($documents, 'delete', function($acl, $role, $resource, $privilege) {
		});

		$intranet = $this->addResource('intranet');
		$this->addPermission($intranet, 'access');
		$this->addPermission($intranet, 'update');

		$salesModule = $this->addResource('salesModule', $this->getResource('intranet'));
		$this->addPermission($salesModule, IAuthorizator::ALL);
		$this->addPermission($salesModule, 'access');
		$this->addPermission($salesModule, 'update');

		$servers = $this->addResource('servers', $this->getResource('intranet'));
		$this->addPermission($servers, IAuthorizator::ALL);
		$this->addPermission($servers, 'access');
		$this->addPermission($servers, 'restart');
		$this->addPermission($servers, 'powerOff');

		$databaseFarm = $this->addResource('databaseFarm', $this->getResource('servers'));
		$this->addPermission($databaseFarm, IAuthorizator::ALL);
		$this->addPermission($databaseFarm, 'access');
		$this->addPermission($databaseFarm, 'restart');
		$this->addPermission($databaseFarm, 'powerOff');
	}


	/**
	 * @param string $resourceName
	 * @param Entities\IResource|NULL $parent
	 * @return Entities\Resource
	 */
	public function addResource($resourceName, Entities\IResource $parent = NULL)
	{
		if (array_key_exists($resourceName, $this->resources)) {
			throw new Exceptions\InvalidStateException("Resource \"$resourceName\" is already registered");
		}

		$resource = new Entities\Resource($resourceName);
		if ($parent) $resource->setParent($parent);

		$this->resources[$resourceName] = $resource;

		return $resource;
	}


	/**
	 * @param string $resourceName
	 * @return Entities\IResource
	 */
	public function getResource($resourceName)
	{
		if (!array_key_exists($resourceName, $this->resources)) {
			throw new Exceptions\InvalidStateException("Resource \"$resourceName\" is not registered");
		}

		return $this->resources[$resourceName];
	}


	/**
	 * @param Entities\IResource|NULL $resource
	 * @param string|NULL $privilege
	 * @param callable|NULL $assertion
	 */
	public function addPermission(Entities\IResource $resource = NULL, $privilege = NULL, callable $assertion = NULL)
	{
		$permission = new Entities\Permission($resource, $privilege, $assertion);
		$permissionStr = (string) $permission;

		if (array_key_exists($permissionStr, $this->permissions)) {
			throw new Exceptions\InvalidStateException("Permission \"$permissionStr\" is already registered");
		}

		$this->permissions[$permissionStr] = $permission;
	}


	/**
	 * @return Entities\IPermission[]
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}


	/**
	 * @return Entities\IResource[]
	 */
	public function getResources()
	{
		return $this->resources;
	}
}
