<?php
/**
 * Test: IPub\Security\Annotations
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


class AnnotationsTest extends Tester\TestCase
{
	/** @var Security\Permission */
	private $permission;

	/** @var Nette\Application\IPresenterFactory */
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
	 * @return array[]
	 */
	public function dataGuestUsers()
	{
		return [
			['guest']
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
		$authenticator = new Nette\Security\SimpleAuthenticator([
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
	public function testCheckUser($username, $password)
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Try to login user
		$this->user->login($username, $password);

		// Create GET request
		$request = new Application\Request('Test', 'GET', array('action' => 'user'));
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
	public function testCheckResourcePrivilege($username, $password)
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Try to login user
		$this->user->login($username, $password);

		// Create GET request
		$request = new Application\Request('Test', 'GET', array('action' => 'resourcePrivilege'));
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
	public function testCheckPermission($username, $password)
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Try to login user
		$this->user->login($username, $password);

		// Create GET request
		$request = new Application\Request('Test', 'GET', array('action' => 'permission'));
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
	public function testCheckRole($username, $password)
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Try to login user
		$this->user->login($username, $password);

		// Create GET request
		$request = new Application\Request('Test', 'GET', array('action' => 'role'));
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
	public function checkLoginRedirect($username, $password)
	{
	    // Create test presenter
        $presenter = $this->createPresenter();

       	// Try to login user
        $this->user->login($username, $password);

        // Create GET request
        $request = new Application\Request('Test', 'GET', array('action' => 'redirect'));
        // & fire presenter & catch response
        $response = $presenter->run($request);

        // Logout user
        $this->user->logout(TRUE);

        Assert::true($response instanceof Application\Responses\TextResponse);
        Assert::equal('Passed', $response->getSource());
	}
}


class TestPresenter extends UI\Presenter
{
	use Security\TPermission;

    public $loginUrl = 'Test:login';

	/**
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function renderUser()
	{
		$this->sendResponse(new Application\Responses\TextResponse('Passed'));
	}


	/**
	 * @Secured
	 * @Secured\Resource(climatisation)
	 * @Secured\Privilege(turnOn)
	 */
	public function renderResourcePrivilege()
	{
		$this->sendResponse(new Application\Responses\TextResponse('Passed'));
	}


	/**
	 * @Secured
	 * @Secured\Permission(climatisation:turnOff)
	 */
	public function renderPermission()
	{
		$this->sendResponse(new Application\Responses\TextResponse('Passed'));
	}


	/**
	 * @Secured
	 * @Secured\Role(authenticated)
	 */
	public function renderRole()
	{
		$this->sendResponse(new Application\Responses\TextResponse('Passed'));
	}


	/**
	* @Secured
	* @Secured\LoginRedirect
	*/
	public function renderRedirect()
	{
		$this->sendResponse(new Application\Responses\TextResponse('Passed'));
	}


	public function renderLogin()
	{
        $this->sendResponse(new Application\Responses\TextResponse('Passed'));
	}
}


\run(new AnnotationsTest());
