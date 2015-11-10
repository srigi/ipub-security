<?php
/**
 * IRolesModel.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPub:Security!
 * @subpackage	Models
 * @since		5.0
 *
 * @date		10.10.14
 */

namespace IPub\Security\Models;

use IPub;
use IPub\Security;

interface IRolesModel
{
	/**
	 * @return Security\Entities\IRole[]
	 */
	public function findAll();
}
