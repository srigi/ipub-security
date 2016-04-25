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


	/**
	 * @param Permission $permission
	 * @param Access\ICheckRequirements $requirementsChecker
	 */
	public function injectPermission(
		Security\Permission $permission,
		Access\ICheckRequirements $requirementsChecker
	) {
		$this->permission			= $permission;
		$this->requirementsChecker	= $requirementsChecker;
	}


	/**
	 * @param $element
	 * @throws Application\ForbiddenRequestException
	 */
	public function checkRequirements($element)
	{
		$redirectUrl = $this->permission->getRedirectUrl();

		try {
			parent::checkRequirements($element);
		} catch(Application\ForbiddenRequestException $e) {
			if ($redirectUrl) {
				$this->presenter->redirect($redirectUrl);
			} else {
				throw $e;
			}
		}

		if (!$this->requirementsChecker->isAllowed($element)) {
			if ($redirectUrl) {
				$this->presenter->redirect($redirectUrl);
			} else {
				throw new Application\ForbiddenRequestException;
			}
		}
	}
}
