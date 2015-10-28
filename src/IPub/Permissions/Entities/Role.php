<?php
/**
 * Role.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Permissions!
 * @subpackage	Entities
 * @since		5.0
 *
 * @date		12.03.14
 */

namespace IPub\Permissions\Entities;

use Nette;

class Role extends Nette\Object implements IRole
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var IRole
	 */
	protected $parent;

	/**
	 * @var IRole[]
	 */
	protected $children = [];

	/**
	 * @var string
	 */
	protected $keyName;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $comment;

	/**
	 * @var int
	 */
	protected $priority = 0;

	/**
	 * @var string[]
	 */
	protected $permissions = [];

	/**
	 * @param IRole $parent
	 * @return $this
	 */
	public function setParent(IRole $parent)
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * @return IRole
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setChildren($roles)
	{
		if (!is_array($roles)) {
			throw new Nette\InvalidArgumentException('You must provide array of children');
		}

		$this->children = array_merge($this->children, $roles);

		return $this;
	}

	/**
	 * @return IRole[]
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * @param string $keyName
	 * @return $this
	 */
	public function setKeyName($keyName)
	{
		$this->keyName = (string) $keyName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getKeyName()
	{
		return $this->keyName;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = (string) $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $comment
	 * @return $this
	 */
	public function setComment($comment)
	{
		$this->comment = (string) $comment;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @param int $priority
	 * @return $this
	 */
	public function setPriority($priority)
	{
		$this->priority = (int) $priority;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * @param array $permissions
	 * @return $this
	 */
	public function setPermissions(array $permissions)
	{
		$this->permissions = $permissions;

		return $this;
	}

	/**
	 * @param string $permission
	 * @return $this
	 */
	public function addPermission($permission)
	{
		$this->permissions[] = (string) $permission;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function clearPermissions()
	{
		$this->permissions = [];

		return $this;
	}

	/**
	 * @param string $permission
	 * @return bool
	 */
	public function hasPermission($permission)
	{
		return in_array((string) $permission, $this->permissions);
	}

	/**
	 * @return \string[]
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}

	/**
	 * @return bool
	 */
	public function isLocked()
	{
		return in_array($this->keyName, [self::ROLE_ANONYMOUS, self::ROLE_AUTHENTICATED, self::ROLE_ADMINISTRATOR]);
	}

	/**
	 * @return bool
	 */
	public function isAnonymous()
	{
		return $this->keyName == self::ROLE_ANONYMOUS;
	}

	/**
	 * @return bool
	 */
	public function isAuthenticated()
	{
		return $this->keyName == self::ROLE_AUTHENTICATED;
	}

	/**
	 * @return bool
	 */
	public function isAdministrator()
	{
		return $this->keyName == self::ROLE_ADMINISTRATOR;
	}

	/**
	 * Convert role object to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}
}
