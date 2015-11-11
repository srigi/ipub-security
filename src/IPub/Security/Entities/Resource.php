<?php

namespace IPub\Security\Entities;

use Nette;


class Resource extends Nette\Object implements IResource
{
	/** @var string */
	private $name;

	/** @var IResource */
	private $parent;

	/** @var IResource[] */
	private $children = [];

	/** @var string */
	private $comment;


	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = (string) $name;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param IResource $parent
	 * @return $this
	 */
	public function setParent(IResource $parent)
	{
		$this->parent = $parent;
		$parent->addChildren($this);

		return $this;
	}


	/**
	 * @return IResource
	 */
	public function getParent()
	{
		return $this->parent;
	}


	/**
	 * @param IResource $children
	 */
	protected function addChildren(IResource $children)
	{
		array_push($this->children, $children);
	}


	/**
	 * @return IResource[]
	 */
	public function getChildren()
	{
		return $this->children;
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
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}
}
