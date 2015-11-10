<?php
/**
 * IChecker.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPub:Security!
 * @subpackage	Access
 * @since		5.0
 *
 * @date		13.10.14
 */

namespace IPub\Security\Access;

interface IChecker
{
	/**
	 * @param $element
	 *
	 * @return bool
	 */
	function isAllowed($element);
}
