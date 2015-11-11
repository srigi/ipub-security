<?php

namespace IPub\Security\Entities;


interface IPermission
{
	const DELIMITER = ':';


	/**
	 * @return IResource|NULL
	 */
	public function getResource();


	/**
	 * @return string|NULL
	 */
	public function getPrivilege();


	/**
	 * @return callable|NULL
	 */
	public function getAssertion();


	/**
	 * @param string $comment
	 * @return $this
	 */
	public function setComment($comment);


	/**
	 * @return string|NULL
	 */
	public function getComment();
}
