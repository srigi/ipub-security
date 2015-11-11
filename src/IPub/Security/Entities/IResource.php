<?php

namespace IPub\Security\Entities;


interface IResource
{
	/**
	 * @return string
	 */
	public function getName();


	/**
	 * @param IResource $parent
	 * @return $this
	 */
	public function setParent(IResource $parent);


	/**
	 * @return IResource
	 */
	public function getParent();


	/**
	 * @return IResource[]
	 */
	public function getChildren();


	/**
	 * @param string $comment
	 * @return $this
	 */
	public function setComment($comment);


	/**
	 * @return string
	 */
	public function getComment();
}
