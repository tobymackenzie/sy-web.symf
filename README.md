Symfony Standard Edition Bundle
===============================

This bundle attempts to hold as much of the base logic / functionality of [Symfony Standard Edition](https://github.com/symfony/symfony-standard) as reasonable to allow it to be used by a more abstracted and minimal equivalent of the Standard Edition.  When logic changes in the Standard Edition, it can be changed in this bundle.  Then your app can upgrade this bundle and get those changes.

Note that some things can't reasonably be stored in this bundle and must still be manually updated.

- Some things things need to be able to run without vendor libraries installed, and thus wouldn't have this bundle available.  For example,  [check.php](https://github.com/symfony/symfony-standard/blob/master/app/check.php) and [config.php](https://github.com/symfony/symfony-standard/blob/master/web/config.php).
- Many non-PHP things aren't easy to accomodate, like htaccess files.
- Putting all of the dependencies in 'composer.json' would limit this bundle's usefulness if you don't want them all.  For this reason, I have most of them under the 'require-dev' and 'suggest' keys so that you could run the production version of your app without componenents you don't need.  I may even remove them from 'require-dev' just to avoid confusion.

Installation and Usage
----------------------

This project is on Packagist as [tjm/symfony-standard-edition-bundle](https://packagist.org/packages/tjm/symfony-standard-edition-bundle), so you can just add "tjm/symfony-standard-edition-bundle": "2.3.*@dev" to your 'composer.json' 'require' items.  Then you can use whatever bits you want.  My [Symfony Initial](https://github.com/tobymackenzie/Symfony-Initial) project uses everything from this project and can be used as an example usage, or as a starting point if you are starting a new project.

### Config ###

The [Config](https://github.com/tobymackenzie/symfony-StandardEditionBundle/blob/master/Component/Config.php) class is used for configuring the environment and debug mode of your app and some of the paths.  Everything is done with static properties / methods so that the class doesn't need to be instantiated (sort of a superglobal singleton).  It extends the class `TJM\Bundle\StandardEditionBundle\Component`, so you can declare this class and set properties on it directly if you need to set some settings before the Config class is available (ie before an autoloader is loaded) and they will be "inherited" by the Config class.

The following class shows the important values:

```php
class Config{
	public static $environment = 'prod'; //--environment symfony is to run in.  normally one of 'prod', 'dev', or 'test'.  Defaults to 'prod'.
	public static $debug; //--boolean of whether we want debug mode on.  if not set, will be true if $environment is not 'prod'.
	public static $paths = Array(
		'app'=> '/filesystem/path/symfony/app'
		,'vendor'=> '/filesystem/path/symfony/vendor'
	); //--paths to 'app' and 'vendor' folders
}
```

There are other settings, but they are things that are no longer used or were there to potentially be used.

### App ###

There are two classes, [ConsoleApp](https://github.com/tobymackenzie/symfony-StandardEditionBundle/blob/master/Component/App/ConsoleApp.php) and [WebApp](https://github.com/tobymackenzie/symfony-StandardEditionBundle/blob/master/Component/App/WebApp.php), meant to hold the logic of 'app/console' and 'web/app*.php' respectively.  They both have static `run()` methods that use values in the Config class to perform basically the funcionality their Standard Edition equivalents do after autoloading and bootstrapping.  This means that 'app/console' can be as simple as:

```php
<?php
use TJM\Bundle\StandardEditionBundle\Component\Config;
use TJM\Bundle\StandardEditionBundle\Component\App\ConsoleApp;

require_once(__DIR__ . '/init.php'); //--load autoloader and bootstrap and set up Config

ConsoleApp::run();
```

and 'app.php' can be as simple as:

```php
<?php
use TJM\Bundle\StandardEditionBundle\Component\App\WebApp;
use TJM\Bundle\StandardEditionBundle\Component\Config;

require_once __DIR__ . '/../app/init.php'; //--load autoloader and bootstrap and set up Config

//--prevent access to 'dev' environment if desired with a check on `Config::getEnvironment() === 'dev'`
//--or load cacheing stuff in 'prod' environment if desired with a check on `Config::getEnvironment() === 'prod'`
//--will try to put this logic in and have it configurable at some point

WebApp::run();
```

### AppKernel ###

Have your AppKernel extend this [AppKernel](https://github.com/tobymackenzie/symfony-StandardEditionBundle/blob/master/Component/AppKernel.php) (namespace `TJM\Bundle\StandardEditionBundle\Component`).  Then `registerContainerConfiguration()` will have the default Standard Edition behavior and you can call `$bundles = parent::registerBundles()` from your `registerBundles()` to get an array of all the bundles that would be registered in Standard Edition, to which you can add whatever you want.

### Symfony config ###

The [config folder](https://github.com/tobymackenzie/symfony-StandardEditionBundle/tree/master/Resources/config) has most of the configuration from Standard Edition.  I tried to avoid putting anything that can't be overridden, like security stuff, and may have not included some things I didn't think were necessary (though I will try to put them back in if anyone wants them there).  You can import them into your configuration from under '@TJMStandardEditionBundle/Resources/config'.

### ScriptHandler ###

There is a [ScriptHandler]() class that runs the post install and post update scripts that Symfony Standard runs after performing those actions with composer.

### Other ###

There's some other, less important stuff here that I may note at some point.

License
-------

BSD (3-clause)
