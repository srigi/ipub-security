# srigi/ipub-security

[![Build Status](https://travis-ci.org/srigi/ipub-security.svg?branch=master?style=flat-square)](https://github.com/srigi/ipub-security)
[![Latest Stable Version](https://img.shields.io/packagist/v/srigi/ipub-security.svg?style=flat-square)](https://packagist.org/packages/srigi/ipub-security)
[![Composer Downloads](https://img.shields.io/packagist/dt/srigi/ipub-security.svg?style=flat-square)](https://packagist.org/packages/srigi/ipub-security)

ACL permissions setter & checker for [Nette Framework](http://nette.org/).

`srigi/ipub-security` is a library that allows easy configuration of Nette&nbsp;Framework ACL system. It supports roles & resources inheritance and also permission assertions are supported.

## Installation
The best way to install `srigi/ipub-security` is by using [Composer](http://getcomposer.org/). To get the latest version of the library run this comment at the root of your project:

```
$ composer require srigi/ipub-security
```

Or you can specify dependency by hand:

```json
{
	"require": {
		"srigi/ipub-security": "^1.2.0"
	}
}
```

## Setup
After installation you need to register the DI extension. If your'e using Nette&nbsp;2.3, you can do that by configuration:

```neon
extensions:
	permission: IPub\Security\DI\SecurityExtension
```

I case of Nette&nbsp;2.2 register extension in your `bootstrap.php`:

```php
$configurator = new Nette\Configurator;
// ...some other code

IPub\Security\DI\SecurityExtension::register($configurator);
```

### The ACL system 101
Nette ACL system brings some terminology you should know befor continuing. First there are *resources* that one (a *role*) wants to access (*privilege*). This forms a *permission*. Example is the best teacher:

**resources** - `intranet`, `serversDashboard`, `databaseServersDashboard`

**roles** - `guest`, `authenticated`, `employee`, `engineer`, `admininstrator`

**privileges** - `access`, `powerOn`, `powerOff`, `reboot`

**permission** - this is just abstract concept when you combine above three entities:

- `authenticated` can `access` the `intranet`
- `engineer` can `reboot` the `serversDashboard`
- `administrator` can do `ALL` on `ALL`

*resources* and *roles* can inherit from one each other and create hierarchies:

```
    intranet
    ├ salesModule
    └ serversDashboard
      └ databaseServersDashboard
```
```
    administrator
    guest
    └ authenticated
      └ employee
        ├ sales
        └ engineer
          └ backend-engineer
```

If there is a permission (combination of `resource`, `role` and `privilege`) registered, this inherits down. In our little example `engineer` can `access` the `intranet` because is inheriting this permission from `authenticated`.

More on this can be found in [access control](https://doc.nette.org/en/2.3/access-control) chapter of Nette&nbsp;Framework documentation.

### Creating permissions
Permission is represented by instance of `IPub\Security\Entities\IPermission`. Such instance is providing a `IPub\Security\Entities\IResource` resource instance, a privilege (defined as string) and assertion (defined as callable). All three components of the permission are optional.

This permissions definitions must be provided by service implementing `IPub\Security\Providers\IPermissionsProvider`. This library is providing such provider service you can use in your project. Or you can write your own.

Defining set of permissions with our `PermissionsProvider` is very easy:

```php
class MyPermissionsProvider extends IPub\Security\Providers\PermissionsProvider
{
	public function __construct()
	{
		$intranet = $this->addResource('intranet');
		$this->addPermission($intranet, Nette\Security\IAuhtorizator::ALL);
		$this->addPermission($intranet, 'access');
		$this->addPermission($intranet, 'update');

		$salesModule = $this->addResource('salesModule', $this->getResource('intranet'));
		$this->addPermission($salesModule, 'access');
		$this->addPermission($salesModule, 'edit', function($acl, $role, $resource, $privilege) {
			// ...code of permission assertion
		});
		...
	}
}
```

Now just register your permission provider:

```neon
services:
	- MyPermissionsProvider
```

### Creating roles & assigning permissions
Similarly as permission also roles have its own interface and needs a provider service. This provider should also assign permissions to the role:

```php
class MyRolesProvider extends IPub\Security\Providers\RolesProvider
{
	/**
	 * @param MyPermissionsProvider $permissionsProvider
	 */
	public function __construct(MyPermissionsProvider $permissionsProvider)
	{
		$permissions = $permissionsProvider->getPermissions();

		$this->addRole(Entities\IRole::ROLE_ADMINISTRATOR);
		$this->addRole(Entities\IRole::ROLE_ANONYMOUS);
		$this->addRole(Entities\IRole::ROLE_AUTHENTICATED, $this->getRole(Entities\IRole::ROLE_ANONYMOUS), $permissions['intranet:access']);

		$this->addRole('employee', $this->getRole(Entities\IRole::ROLE_AUTHENTICATED));
		$this->addRole('sales', $this->getRole('employee'), [
			$permissions['salesModule:'],
		]);
		$this->addRole('engineer', $this->getRole('employee'), [
			$permissions['servers:access'],
		]);

		// ...more roles & permissions assignments
	}

```
Don't forget to register your roles provider:

```neon
services:
	- MyRolesProvider
```

Now your'e set!

## Checking permissions
Library provide a PHP trait, which enables pleasant quering Nette ACL system we've just configured. Please note that traits are available from PHP&nbsp;5.4, for older versions of PHP you must copy/paste trait contents. This trait is effective only in presenter(s).

```php
class BasePresenter extends Nette\Application\UI\Presenter
{
	use IPub\Security\TPermission;
}
```

### Using annotations
You can fine-tune checking logic by this set of annotations:

```php
/**
 * @Secured
 * @Secured\User(loggedIn)
 * @Secured\Resource(RESOURCE_NAME)
 * @Secured\Privilege(PRIVILEGE_NAME)
 * @Secured\Permission(RESOURCE_NAME: PRIVILEGE_NAME)
 * @Secured\Role(ROLE_NAME)
 */
class IntranetPresenter extends BasePresenter
{
	/**
	 * @Secured
	 * @Secured\Permission(RESOURCE_NAME: PRIVILEGE_NAME)
	 */
	public function renderDefault()
	{
	}
}
```

#### `@Secured`
This annotation instruct security system that presenter is subject to the permissions check. Without it permission checking will be skipped completely!

#### `@Secured\User`
This annotation accept value `loggedIn` or `guest`. Access to any `resource` and any `privilege` is controled only by login state of the current user.

--

Next annotations are working over `Nette\Security\User` roles assigned during login process.

#### `@Secured\Resource`
Access is granted only if role is allowed to access specified `resource`.

#### `@Secured\Privilege`
This grand access only if role is allowed to access specified `privilege`.

#### `@Secured\Permission`
Combination of above two - access is granted only if role have `resource: privilege` permission.

#### `@Secured\Role`
Grand access only to specified `role`.

One every place where `*_NAME` applies, you can specify multiple names separated by comma.

### Using in presenters, components, models, etc.
Permission check can be performed also manually. You just need `Nette\Security\User` instance on which you call:

```php
$user->isAllowed('resource', 'privilege');
```

`TRUE` of `FALSE` is returned respecively.


### Using in Latte

In latte you can use two special macros.

```html
<p>This text is for everyone...</p>

{ifAllowed resource => 'intranet', privilege => 'access'}
	<p>But this one is only for special persons...</p>
{/ifAllowed}
```

Macro **ifAllowed** is very similar to annotations definitions. You can use here one or all of available parameters: user, resource, privilege, permission or role.

This macro can be also used as **n:** macro:

```html
<p>This text is for everyone...</p>
<p n:ifAllowed resource => 'intranet', privilege => 'access'>
	But this one is only for special persons...</p>
```

And second special macro is for links:

```html
<a n:allowedHref="Intranet:">Link to Intranet...</a>
```

Macro **n:allowedHref** is expecting only valid link and in case user doesn't have permission to that resource, link isn't displayed.

## TODO
- check `Entities\Permission` constructor types
- make documentation examples to be in sync w/ tests
- latte macros tests
- check annotations test logic
- permissions-assertions tests/doc
- `RolesProvider::allow`, `RolesProvider::deny` methods

## History
- 1.3.0 Rename `RolesModel` and `IPub\Security\Models` to `RolesProvider` and `IPub\Security\Providers`
- 1.2.0 Rewrite `Security\Permission` to support resource inheritance & permissions assertions
- 1.1.0 Cloned library into `srigi/ipub-permissions`
- 1.0.1 Added roles inheritance

## License
New BSD License or the GNU General Public License (GPL) version 2 or 3, see [license.md](https://github.com/srigi/ipub-security/blob/master/license.md).
