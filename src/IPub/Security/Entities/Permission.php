<?php

namespace IPub\Security\Entities;

use Nette;
use Nette\Security\IAuthorizator;
use IPub\Security\Entities;
use IPub\Security\Exceptions;


class Permission extends Nette\Object implements IPermission
{
	/** @var Entities\IResource|NULL */
	protected $resource;

	/** @var string|NULL */
	protected $privilege;

	/** @var callable|NULL */
	protected $assertion;

	/** @var string */
	private $comment;


	/**
	 * @param IResource|NULL $resource
	 * @param string|NULL $privilege
	 * @param callable|NULL $assertion
	 */
	public function __construct(Entities\IResource $resource = NULL, $privilege = NULL, callable $assertion = NULL)
	{
		if (!($resource instanceof Entities\IResource) && ($resource !== IAuthorizator::ALL)) {
			new Exceptions\InvalidArgumentException('Resource must be either IResource or Nette\Security\IAuthorizator::ALL');
		}

		if (!is_string($privilege) && ($privilege !== IAuthorizator::ALL)) {
			new Exceptions\InvalidArgumentException('Privilege must be either string or Nette\Security\IAuthorizator::ALL');
		}

		$this->resource = $resource;
		$this->privilege = $privilege;
		$this->assertion = $assertion;
	}


	/**
	 * @return IResource|NULL
	 */
	public function getResource()
	{
		return $this->resource;
	}


	/**
	 * @return string|NULL
	 */
	public function getPrivilege()
	{
		return $this->privilege;
	}


	/**
	 * @return callable|NULL
	 */
	public function getAssertion()
	{
		return $this->assertion;
	}


	/**
	 * @param string $comment
	 * @return $this
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;

		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getComment()
	{
		return $this->comment;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		$ky = (string) $this->resource . IPermission::DELIMITER . (string) $this->privilege;

		return $ky;
	}
}
