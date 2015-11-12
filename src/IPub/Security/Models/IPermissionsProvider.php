<?php

namespace IPub\Security\Models;

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
