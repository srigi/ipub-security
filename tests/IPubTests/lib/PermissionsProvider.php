<?php

namespace IPubTests;

use Nette\Security\IAuthorizator;
use IPub\Security;
use IPub\Security\Entities;


class PermissionsProvider extends Security\Providers\PermissionsProvider
{
	/**
	 * Resources are defined like this:
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
}
