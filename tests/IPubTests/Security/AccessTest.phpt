<?php
/**
 * Test: IPub\Security\Access
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPub:Security!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		14.01.15
 */

namespace IPubTests\Security;

use Nette;
use Nette\Application;
use Nette\Application\UI;
use Nette\Security as NS;

use Tester;
use Tester\Assert;

use IPub\Security;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../lib/PermissionsProvider.php';
require __DIR__ . '/../lib/RolesProvider.php';


class AccessTest extends Tester\TestCase
{
	/** @var Security\Permission */
	private $permission;

	/** @var Application\IPresenterFactory */
	private $presenterFactory;

	/** @var NS\User */
	private $user;


	/**
	 * @return array[]
	 */
	public function dataRegisteredUsers()
	{
		return [
			['john', '123456'],
			['jane', '123456'],
		];
	}


	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addConfig(__DIR__ . '/../config/application.neon', $config::NONE);
		$config->addConfig(__DIR__ . '/../config/presenters.neon', $config::NONE);
		$config->addConfig(__DIR__ . '/../config/providers.neon', $config::NONE);

		Security\DI\SecurityExtension::register($config);

		return $config->createContainer();
	}


	/**
	 * @return Application\IPresenter
	 */
	protected function createPresenter()
	{
		// Create test presenter
		$presenter = $this->presenterFactory->createPresenter('Test');
		// Disable auto canonicalize to prevent redirection
		$presenter->autoCanonicalize = FALSE;

		return $presenter;
	}


	public function setUp()
	{
		parent::setUp();

		$container = $this->createContainer();

		// Create user authenticator
		$authenticator = new NS\SimpleAuthenticator([
				'john'	=> '123456',
				'jane'	=> '123456',
		], [
				'john'	=> [
						Security\Entities\IRole::ROLE_AUTHENTICATED
				],
				'jane'	=> [
						Security\Entities\IRole::ROLE_AUTHENTICATED,
						Security\Entities\IRole::ROLE_ADMINISTRATOR
				]
		]);

		// Get permissions service
		$this->permission = $container->getService('ipubSecurity.permission');

		// Get presenter factory from container
		$this->presenterFactory = $container->getByType('Nette\Application\IPresenterFactory');

		// Get application user
		$this->user = $container->getService('user');
		$this->user->setAuthenticator($authenticator);
	}


	/**
	 * @dataProvider dataRegisteredUsers
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function testPresenterActionAllowed($username, $password)
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Try to login user
		$this->user->login($username, $password);

		// Create GET request
		$request = new Application\Request('Test', 'GET', array('action' => 'allowed'));
		// & fire presenter & catch response
		$response = $presenter->run($request);

		// Logout user
		$this->user->logout(TRUE);

		Assert::true($response instanceof Application\Responses\TextResponse);
		Assert::equal('Passed', $response->getSource());
	}


	/**
	 * @dataProvider dataRegisteredUsers
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function testPresenterActionAllowedRole($username, $password)
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Try to login user
		$this->user->login($username, $password);

		// Create GET request
		$request = new Application\Request('Test', 'GET', array('action' => 'allowedRole'));
		// & fire presenter & catch response
		$response = $presenter->run($request);

		// Logout user
		$this->user->logout(TRUE);

		Assert::true($response instanceof Application\Responses\TextResponse);
		Assert::equal('Passed', $response->getSource());
	}


	/**
	 * @throws Nette\Application\ForbiddenRequestException
	 */
	public function testPresenterActionNotAllowed()
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Create GET request
		$request = new Application\Request('Test', 'GET', array('action' => 'allowedRole'));
		// & fire presenter
		$presenter->run($request);
	}


	/**
	 * @dataProvider dataRegisteredUsers
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @throws Nette\Application\ForbiddenRequestException
	 */
	public function testNotAllowedLoggedIn($username, $password)
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Try to login user
		$this->user->login($username, $password);

		// Create GET request
		$request = new Application\Request('Test', 'GET', array('action' => 'onlyGuest'));
		// & fire presenter & catch
		$presenter->run($request);
	}


	public function testAllowedGuest()
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Create GET request
		$request = new Application\Request('Test', 'GET', array('action' => 'onlyGuest'));
		// & fire presenter & catch response
		$response = $presenter->run($request);

		Assert::true($response instanceof Application\Responses\TextResponse);
		Assert::equal('Passed', $response->getSource());
	}
}


/**
 * @Secured
 * @Secured\Resource(intranet)
 * @Secured\Privilege(access)
 */
class TestPresenter extends UI\Presenter
{
	use Security\TPermission;


	public function renderAllowed()
	{
		$this->sendResponse(new Application\Responses\TextResponse('Passed'));
	}


	/**
	 * @Secured
	 * @Secured\Role(authenticated, administrator)
	 */
	public function renderAllowedRole()
	{
		$this->sendResponse(new Application\Responses\TextResponse('Passed'));
	}


	/**
	 * @Secured
	 * @Secured\User(guest)
	 */
	public function renderOnlyGuest()
	{
		$this->sendResponse(new Application\Responses\TextResponse('Passed'));
	}
}


\run(new AccessTest());
