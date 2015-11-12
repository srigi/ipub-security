<?php

namespace IPub\Security\Providers;

use IPub\Security\Entities;


interface IPermissionsProvider
{
	/**
	 * @return Entities\IPermission[]
	 */
	public function getPermissions();


	/**
	 * @return Entities\IResource[]
	 */
	public function getResources();
}
