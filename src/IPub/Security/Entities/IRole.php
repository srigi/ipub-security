<?php
/**
 * IRole.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPub:Security!
 * @subpackage	Entities
 * @since		5.0
 *
 * @date		12.03.14
 */

namespace IPub\Security\Entities;


interface IRole
{
	/**
	 * The identifier of the anonymous role
	 *
	 * @var string
	 */
	const ROLE_ANONYMOUS = 'guest';

	/**
	 * The identifier of the authenticated role
	 *
	 * @var string
	 */
	const ROLE_AUTHENTICATED = 'authenticated';

	/**
	 * The identifier of the administrator role
	 *
	 * @var string
	 */
	const ROLE_ADMINISTRATOR = 'administrator';


	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @param IRole[] $parents
	 *
	 * @return $this
	 */
	public function setParents($parents);

	/**
	 * @return IRole[]|NULL
	 */
	public function getParents();

	/**
	 * @return IRole[]
	 */
	public function getChildren();

	/**
	 * @param string $comment
	 *
	 * @return $this
	 */
	public function setComment($comment);

	/**
	 * @return string
	 */
	public function getComment();

	/**
	 * Set permissions for the role
	 *
	 * @param array $permissions
	 *
	 * @return $this
	 */
	public function setPermissions(array $permissions);

	/**
	 * Add one permission to the role
	 *
	 * @param string $permission
	 */
	public function addPermission($permission);

	/**
	 * Clear all role permissions
	 *
	 * @return $this
	 */
	public function clearPermissions();

	/**
	 * Checks if a permission is set for this role
	 *
	 * @param  string $permission
	 *
	 * @return bool
	 */
	public function hasPermission($permission);

	/**
	 * Returns permissions for the role
	 *
	 * @return string[]
	 */
	public function getPermissions();

	/**
	 * Check if role is one from system roles
	 *
	 * @return bool
	 */
	public function isLocked();

	/**
	 * Check if role is guest
	 *
	 * @return bool
	 */
	public function isAnonymous();

	/**
	 * Check if role is authenticated
	 *
	 * @return bool
	 */
	public function isAuthenticated();

	/**
	 * Check if role is administrator
	 *
	 * @return bool
	 */
	public function isAdministrator();
}
