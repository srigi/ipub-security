<?php
/**
 * TPermission.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPub:Security!
 * @subpackage	common
 * @since		5.0
 *
 * @date		13.10.14
 */

namespace IPub\Security;

use Nette;
use Nette\Application;

use IPub;
use IPub\Security;

trait TPermission
{
	/**
	 * @var Security\Permission
	 */
	protected $permission;

	/**
	 * @var Access\ICheckRequirements
	 */
	protected $requirementsChecker;

	/** @var Security\Access\AnnotationChecker $annotationChecker */
	protected $annotationChecker;

	/**
	 * @param Permission $permission
	 * @param Access\ICheckRequirements $requirementsChecker
	 * @param Access\AnnotationChecker $annotationChecker
	 */
	public function injectPermission(
		Security\Permission $permission,
		Access\ICheckRequirements $requirementsChecker,
		Security\Access\AnnotationChecker $annotationChecker
	) {
		$this->permission			= $permission;
		$this->requirementsChecker	= $requirementsChecker;
		$this->annotationChecker    = $annotationChecker;
	}

	/**
	 * @param $element
	 *
	 * @throws Application\ForbiddenRequestException
	 */
	public function checkRequirements($element)
	{
		parent::checkRequirements($element);
		
		if (!$this->requirementsChecker->isAllowed($element) && $this->annotationChecker->checkLoginRedirect($element)) {
			$this->presenter->redirect($this->presenter->loginUrl);
		}

		if (!$this->requirementsChecker->isAllowed($element)) {
			throw new Application\ForbiddenRequestException;
		}
	}
}
