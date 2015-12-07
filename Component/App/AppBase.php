<?php
namespace TJM\Bundle\StandardEditionBundle\Component\App;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use BadMethodCallException;

class AppBase{
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
	protected function set($opts = Array()){
		if(isset($opts['debug'])){
			$this->setDebug($opts['debug']);
		}
		if(isset($opts['environment'])){
			$this->setEnvironment($opts['environment']);
		}
		if(isset($opts['loader'])){
			$this->setLoader($opts['loader']);
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
	protected function initBundles(){
		$bundles = $this->initStandardEditionBundles();
		$bundles[] = new \TJM\Bundle\StandardEditionBundle\TJMStandardEditionBundle();
		return $bundles;
	}

	/*
	Method: initStandardEditionBundles
	Instantiates Standard Edition bundles and returns them as Array for Symfony's kernel.
	*/
	protected function initStandardEditionBundles(){
		$bundles = array(
			//--standard
			//---framework
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle()
			//---standard symfony
			,new \Symfony\Bundle\SecurityBundle\SecurityBundle()
			,new \Symfony\Bundle\TwigBundle\TwigBundle()
			,new \Symfony\Bundle\MonologBundle\MonologBundle()
			,new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle()
			,new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle()
			,new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle()
		);

		if(in_array($this->getEnvironment(), array('dev', 'test'))) {
			$bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
			$bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
			$bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
		}
		return $bundles;
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
		return $this->getPath('app') . '/config/config_' . $env . '.yml';
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
	protected function createKernel($class = null){
		if(!$class){
			$class = $this->getKernelClass();
		}
		//--load class from app directory if it doesn't exist.  Needed because we can't load AppKernel in autoloader or classes defined in bootstrap will already be defined.
		if(!class_exists($class)){
			require_once($this->getPath('app') . '/' . $class . '.php');
		}
		return new $class($this->getEnvironment(), ($this->getDebug()));
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
	protected $kernelClass = 'AppKernel';
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
			Debug::enable();
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
		if($this->getEnvironment() === 'dev'){
			Debug::enable();
		}

		$kernel = $this->getKernel();
		$kernel->loadClassCache();

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
				case 'project':
					if($this->hasPath('app')){
						$this->setPath('project', $this->getPath('app') . '/..');
					}else{
						$this->setPath('project', ($this->isCli())
							? exec('pwd')
							: $_SERVER['DOCUMENT_ROOT'] . '/..'
						);
					}
				break;
				case 'app':
					$this->setPath('app', $this->getPath('project') . '/app');
				break;
				case 'PHPCLI':
					$this->setPath('PHPCLI', "/usr/bin/env php");
				break;
				case 'src':
					$this->setPath('src', $this->getPath('project') . "/src");
				break;
				case 'vendor':
					$this->setPath('vendor', $this->getPath('project') . "/vendor");
				break;
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
