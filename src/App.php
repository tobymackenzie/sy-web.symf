<?php
namespace TJM\SyWeb;
use BadMethodCallException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Debug as OldDebug;
use Symfony\Component\ErrorHandler\Debug;

class App{
	public function __construct($opts = Array()){
		$opts = array_merge($this->getDefaultOptions(), $opts);

		//--config
		$this->set($opts);

		//--operation
		if(isset($opts['run']) && $opts['run']){
			$this->run();
		}
	}

	/*=====
	==config
	=====*/
	protected $bundlesList = Array(
		'@standard'
	);
	protected function set($opts = Array()){
		if(isset($opts['allowedDevIPs'])){
			$this->setAllowedDevIPs($opts['allowedDevIPs']);
		}
		if(isset($opts['bundles'])){
			$this->setBundlesList($opts['bundles']);
		}
		if(isset($opts['debug'])){
			$this->setDebug($opts['debug']);
		}
		if(isset($opts['environment'])){
			$this->setEnvironment($opts['environment']);
		}
		if(isset($opts['kernel'])){
			if(is_object($opts['kernel'])){
				$this->setKernel($opts['kernel']);
			}else{
				$this->setKernelClass($opts['kernel']);
			}
		}
		if(isset($opts['loader'])){
			$this->setLoader($opts['loader']);
		}
		if(isset($opts['name'])){
			$this->setName($opts['name']);
		}
		if(isset($opts['paths'])){
			$this->mergeInPaths($opts['paths']);
		}
		if(isset($opts['umask'])){
			$this->setUmask($opts['umask']);
		}

		return $this;
	}

	/*
	Method: initBundles
	Instantiates bundles and returns them as Array for Symfony's kernel.  Override to change which bundles are loaded.
	*/
	protected function initBundles($bundles = null){
		if(!$bundles){
			$bundles = $this->bundlesList;
		}
		$initedBundles = [];
		foreach($bundles as $key=> $value){
			//--allow arrays like symfony 4
			if(is_string($key) && is_array($value)){
				if(!in_array($this->getEnvironment(), $value) && $value !== 'all'){
					continue;
				}
				$bundle = $key;
			}else{
				$bundle = $value;
			}
			if(is_object($bundle)){
				$initedBundles[] = $bundle;
			}elseif($bundle === '@standard'){
				$initedBundles = array_merge($initedBundles, $this->initStandardEditionBundles());
			}else{
				if(class_exists($bundle)){
					$initedBundles[] = new $bundle();
				}
			}
		}
		return $initedBundles;
	}

	/*
	Method: initStandardEditionBundles
	Instantiates Standard Edition bundles and returns them as Array for Symfony's kernel.
	*/
	protected function initStandardEditionBundles(){
		$bundles = array(
			//--standard
			//---framework
			'Symfony\Bundle\FrameworkBundle\FrameworkBundle'
			//---standard symfony
			,'Symfony\Bundle\SecurityBundle\SecurityBundle'
			,'Symfony\Bundle\TwigBundle\TwigBundle'
			,'Symfony\Bundle\MonologBundle\MonologBundle'
			,'Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle'
			,'Doctrine\Bundle\DoctrineBundle\DoctrineBundle'
			,'Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle'
		);

		if(in_array($this->getEnvironment(), array('dev', 'test'))) {
			$bundles[] = 'Symfony\Bundle\WebProfilerBundle\WebProfilerBundle';
			$bundles[] = 'Sensio\Bundle\DistributionBundle\SensioDistributionBundle';
			$bundles[] = 'Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle';
		}
		return $this->initBundles($bundles);
	}
	protected function setBundlesList($bundles){
		$this->bundlesList = $bundles;
		return $this;
	}

	/*
	Property: cache
	Tells `runWeb()` whether to use Symfony's `HttpCache` or not.  If set to a non-boolean value, will compare to `$environment` to determine whether to use the cache.  Once instantiated, `$cache` will be the actual `AppCache` object.
	*/
	protected $cache = false;
	protected function getCache(){
		return $this->cache;
	}
	protected function setCache($cache){
		$this->cache = $cache;
		return $this;
	}

	/*
	Method: getConfigPath
	Get path to symfony config file.
	*/
	protected function getConfigPath($env = null){
		if(!$env){
			$env = $this->getEnvironment();
		}
		return $this->getPath('config.' . $env);
	}
	/*
	Property: consoleApp
	Console application instance.
	*/
	protected $consoleApp;
	protected function createConsoleApp(){
		return new Application($this->getKernel());
	}
	protected function getConsoleApp(){
		if(!$this->hasConsoleApp()){
			$this->setConsoleApp($this->createConsoleApp());
		}
		return $this->consoleApp;
	}
	protected function hasConsoleApp(){
		return (isset($this->consoleApp));
	}
	protected function setConsoleApp($consoleApp){
		$this->consoleApp = $consoleApp;
		return $this;
	}

	/*
	Method: getDefaultOptions
	Get default options for constructor.  Override to set default options.
	*/
	protected function getDefaultOptions(){
		return Array();
	}

	/*
	Property: kernel
	*/
	protected $kernel;
	protected function createKernel($class = null, $opts = null, $debug = null){
		if(!$class){
			$class = $this->getKernelClass();
		}
		if(!$opts){
			$opts = $this;
		}
		if(is_a($class, 'TJM\SyWeb\AppKernel', true)){
			return new $class($opts);
		}else{
			return new $class(is_object($opts) ? $opts->getEnvironment() : $opts, $debug ?? $opts->getDebug());
		}
	}
	protected function getKernel(){
		if(!$this->hasKernel()){
			$this->setKernel($this->createKernel());
		}
		return $this->kernel;
	}
	protected function hasKernel(){
		return (isset($this->kernel));
	}
	protected function setKernel($kernel){
		$this->kernel = $kernel;
		return $this;
	}

	/*
	Property: kernelClass
	Class to use when calling `createKernel()`
	*/
	protected $kernelClass = 'TJM\SyWeb\AppKernel';
	protected function getKernelClass(){
		return $this->kernelClass;
	}
	protected function setKernelClass($class){
		$this->kernelClass = $class;
		return $this;
	}

	/*
	Property: loader
	Reference to composer loader or equivalent.
	*/
	protected $loader;
	protected function getLoader(){
		return $this->loader;
	}
	protected function setLoader($loader){
		$this->loader = $loader;
		return $this;
	}

	/*
	Property: umask
	Value to set umask to.  By default, doesn't set it to anything.  See [Setting up permissions](http://symfony.com/doc/current/book/installation.html#configuration-and-setup).
	*/
	protected $umask = false;
	protected function getUmask(){
		return $this->umask;
	}
	protected function setUmask($value){
		if($value === true){
			$value = 0;
		}
		$this->umask = $value;
		umask($this->umask);
		return $this;
	}
	/*=====
	==operation
	=====*/
	/*
	Property: allowedDevIPs
	IPs that can access dev environment
	*/
	protected $allowedDevIPs = ['127.0.0.1', '::1'];
	public function setAllowedDevIPs(array $value = null){
		$this->allowedDevIPs = $value;
	}

	/*
	Method: isAllowedToRunWeb
	Check if we're allowed to run web.  By default this blocks requests for dev environment except on localhost.
	*/
	protected function isAllowedToRunWeb(){
		if(
			$this->getEnvironment() === 'dev'
			&& (
				isset($_SERVER['HTTP_CLIENT_IP'])
				|| isset($_SERVER['HTTP_X_FORWARDED_FOR'])
				|| !(in_array(@$_SERVER['REMOTE_ADDR'], $this->allowedDevIPs))
			)
		){
			return false;
		}
		return true;
	}

	protected function enableDebug(){
		if(class_exists(Debug::class)){
			Debug::enable();
		}elseif(class_exists(OldDebug::class)){
			OldDebug::enable();
		}
	}

	/*
	Method: run
	Run application
	*/
	protected function run($type, $opts = Array()){
		switch($type){
			case 'web':
				return $this->runWeb($opts);
			break;
			case 'console':
				return $this->runConsole($opts);
			break;
			default:
				throw new \Exception("Calling `run()` on class 'App'.  Behavior undefined.");
			break;
		}
	}

	/*
	Method: runConsole
	Run console application
	*/
	protected function runConsole(){
		set_time_limit(0);

		$input = new ArgvInput();
		$this->setEnvironment($input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ?: 'dev'));
		if($this->getEnvironment() !== 'prod'){
			$this->setDebug(getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(array('--no-debug', '')));
		}else{
			$this->setDebug(getenv('SYMFONY_DEBUG') !== '0' && $input->hasParameterOption(array('--debug', '')));
		}
		if($this->getDebug()){
			$this->enableDebug();
		}

		$kernel = $this->getKernel();
		$consoleApp = $this->getConsoleApp();
		$consoleApp->run($input);

		return $this;
	}

	/*
	Method: runWeb
	Run web application
	*/
	protected function runWeb($opts = Array()){
		if(!$this->isAllowedToRunWeb()){
			header('HTTP/1.0 403 Forbidden');
			exit('You are not allowed to access this file. Check App for more information.');
		}
		if($this->getEnvironment() === 'dev'){
			$this->enableDebug();
		}

		$kernel = $this->getKernel();

		if($this->getCache() && ($this->getCache() === true || $this->getCache() === $this->getEnvironment())){
			$kernel = new \AppCache($kernel);
			$this->setCache($kernel);
			$this->setKernel($kernel);
		}

		static::processRequest($kernel, $opts);

		return $this;
	}

	/*
	Method: processRequest
	Process a request in the standard edition fashion.
	Parameters:
		kernel(KernelInterface): application kernel
		options(Map):
			cache(Boolean): whether to use an AppCache
			request(Request): specify an alternative request to process
	*/
	static public function processRequest($kernel, $opts = Array()){
		if(isset($opts['request'])){
			$request = $opts['request'];
		}else{
			$request = Request::createFromGlobals();
		}
		$response = $kernel->handle($request);
		$response->send();
		$kernel->terminate($request, $response);
	}

	/*
	Method: getResponse
	Return response for request or path, mainly for CLI usage
	-! Do we want to use this for `runWeb()`?
	*/
	public function getResponse($request = null) :Response{
		if(empty($request)){
			$request = Request::createFromGlobals();
		}elseif(is_string($request)){
			$request = Request::create($request);
		}
		return $this->getKernel()->handle($request);
	}

	/*=====
	==config
	=====*/
	/*
	Property: debug
	Whether to enable Symfony debugging or not.
	*/
	protected $debug;
	protected function getDebug(){
		if(!isset($this->debug)){
			$this->debug = ($this->getEnvironment() !== 'prod');
		}
		return $this->debug;
	}
	protected function setDebug($debug){
		$this->debug = $debug;
		return $this;
	}
	/*
	Property: environment
	Environment for Symfony kernel.
	*/
	protected $environment;
	protected function getEnvironment(){
		if(!isset($this->environment)){
			$this->environment = (defined(__NAMESPACE__ . '\ENVIRONMENT'))
				? constant(__NAMESPACE__ . '\ENVIRONMENT')
				: 'prod'
			;
		}
		return $this->environment;
	}
	protected function setEnvironment($environment){
		$this->environment = $environment;
		return $this;
	}
	/*
	Property: isCli
	Whether app is being run as a CLI application
	*/
	protected $isCli;
	protected function isCli(){
		if(!isset($this->isCli)){
			$this->isCli = (php_sapi_name() == 'cli');
		}
		return $this->isCli;
	}

	/*
	Property: name
	Name of app, as used for cache, logs, front controller, etc.
	*/
	protected $name;
	public function getName(){
		return $this->name;
	}
	protected function setName($value){
		$this->name = $value;
		return $this;
	}

	/*
	Property: paths
	Collection of paths to be used in early lifecycle
	*/
	protected $paths = Array();
	protected function addPaths($paths){
		foreach($paths as $name=> $path){
			if(!$this->hasPath($name)){
				$this->setPath($name, $path);
			}
		}
		return $this;
	}
	protected function getPath($name){
		if($this->hasPath($name)){
			return $this->paths[$name];
		}else{
			//--default paths
			switch($name){
				case 'config':
					$path = $this->getPath('app') . '/config';
				break;
				case 'project':
					if($this->hasPath('app')){
						$path = $this->getPath('app') . '/..';
					}else{
						$path = ($this->isCli())
							? exec('pwd')
							: $_SERVER['DOCUMENT_ROOT'] . '/..'
						;
					}
				break;
				case 'app':
					$path = $this->getPath('project') . '/app';
				break;
				case 'cache':
					$path = $this->getPath('var') . '/cache';
				break;
				case 'cache.env':
					$path = $this->getPath('cache.' . $this->getEnvironment());
				break;
				case 'logs':
					$path = $this->getPath('var') . '/logs' . ($this->getName() ? '/' . $this->getName() : '');
				break;
				case 'PHPCLI':
					$path = "/usr/bin/env php";
				break;
				case 'src':
					$path = $this->getPath('project') . "/src";
				break;
				case 'var':
					$path = $this->getPath('project') . '/var';
				break;
				case 'vendor':
					$path = $this->getPath('project') . "/vendor";
				break;
				default:
					if(preg_match('/^cache\.([\w-]+)/', $name, $matches)){
						$path = $this->getPath('cache') . '/' . ($this->getName() ? $this->getName() . '.' : '') . $matches[1];
					}elseif(preg_match('/^config\.([\w-]+)$/', $name, $matches)){
						$path = $this->getPath('config') . '/config_' . $matches[1] . '.yml';
					}
				break;
			}
			if(isset($path)){
				$this->setPath($name, $path);
			}
			return ($this->hasPath($name)) ? $this->paths[$name] : null;
		}
	}
	protected function hasPath($name){
		return (isset($this->paths[$name]));
	}
	protected function mergeInPaths($paths){
		$this->paths = array_merge($this->paths, $paths);
		return $this;
	}
	protected function setPath($name, $value){
		$this->paths[$name] = $value;
		return $this;
	}
	protected function setPaths($paths){
		$this->paths = $paths;
		return $this;
	}

	/*=====
	==class operation
	=====*/
	/*
	Method: __call
	Call protected / private methods as if they were public.  Allows us to push those methods through `__callStatic()` when in a static context.
	//-@ http://stackoverflow.com/a/3716750
	*/
	public function __call($name, $args){
		if(method_exists($this, $name)){
			return call_user_func_array(Array($this, $name), $args);
		}else{
			throw new BadMethodCallException('Attempting to call ' . $name . '.  Method does not exist.');
		}
	}

	/*
	Static Method: __callStatic
	Call method of singleton instance if method isn't defined.  Note: All methods must be protected and go through `__call()` for this to work properly.  Otherwise PHP will run the non-static methods in a static context and never hit `__callStatic()`.
	*/
	static public function __callStatic($name, $args){
		if(self::$singletonInstance === null && $name === 'set'){
			$instance = static::getSingleton($args[0]);
			return $instance;
		}else{
			return self::callStatic($name, $args);
		}
	}
	static public function callStatic($name, $args = null){
		$instance = static::getSingleton();
		if(method_exists($instance, $name)){
			return call_user_func_array(Array($instance, $name), $args);
		}else{
			throw new BadMethodCallException();
		}
	}
	/*
	Static Method: getSingleton
	Get a singleton instance of this class to allow us to avoid creating a global instance.
	Arguments:
		$opts (Array|Optional): Array of options to pass to the constructor if not instantiated yet.
	*/
	static protected $singletonInstance;
	static public function getSingleton($opts = Array()){
		//-# using 'self' ensures we get an instance of a child class if that is what has been gotten.
		if(self::$singletonInstance === null){
			self::$singletonInstance = new static($opts);
		}elseif(count($opts)){
			throw new \Exception('Singleton already exists.  Cannot pass in options.');
		}
		return self::$singletonInstance;
	}
}
