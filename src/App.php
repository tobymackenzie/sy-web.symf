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
	public function __construct($opts = []){
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
	protected $bundlesList = [
		'@standard'
	];
	public function set($opts = []){
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
		if(isset($opts['isCli'])){
			$this->isCli = $opts['isCli'];
		}
		if(isset($opts['kernel'])){
			if(is_object($opts['kernel'])){
				$this->setKernel($opts['kernel']);
			}else{
				$this->setKernelClass($opts['kernel']);
			}
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
	public function initBundles($bundles = null){
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
		$bundles = [
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
		];

		if(in_array($this->getEnvironment(), ['dev', 'test'])) {
			$bundles[] = 'Symfony\Bundle\WebProfilerBundle\WebProfilerBundle';
			$bundles[] = 'Sensio\Bundle\DistributionBundle\SensioDistributionBundle';
			$bundles[] = 'Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle';
		}
		return $this->initBundles($bundles);
	}
	public function setBundlesList($bundles){
		$this->bundlesList = $bundles;
		return $this;
	}

	/*
	Method: getConfigPath
	Get path to symfony config file.
	*/
	public function getConfigPath($env = null){
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
	public function createConsoleApp(){
		return new Application($this->getKernel());
	}
	public function getConsoleApp(){
		if(!$this->hasConsoleApp()){
			$this->setConsoleApp($this->createConsoleApp());
		}
		return $this->consoleApp;
	}
	public function hasConsoleApp(){
		return (isset($this->consoleApp));
	}
	public function setConsoleApp($consoleApp){
		$this->consoleApp = $consoleApp;
		return $this;
	}

	/*
	Method: getDefaultOptions
	Get default options for constructor.  Override to set default options.
	*/
	protected function getDefaultOptions(){
		return [];
	}

	/*
	Property: kernel
	*/
	protected $kernel;
	public function createKernel($class = null, $opts = null, $debug = null){
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
	public function getKernel(){
		if(!$this->hasKernel()){
			$this->setKernel($this->createKernel());
		}
		return $this->kernel;
	}
	public function hasKernel(){
		return (isset($this->kernel));
	}
	public function setKernel($kernel){
		$this->kernel = $kernel;
		return $this;
	}

	/*
	Property: kernelClass
	Class to use when calling `createKernel()`
	*/
	protected $kernelClass = 'TJM\SyWeb\AppKernel';
	public function getKernelClass(){
		return $this->kernelClass;
	}
	public function setKernelClass($class){
		$this->kernelClass = $class;
		return $this;
	}

	/*
	Property: umask
	Value to set umask to.  By default, doesn't set it to anything.  See [Setting up permissions](http://symfony.com/doc/current/book/installation.html#configuration-and-setup).
	*/
	protected $umask = false;
	public function getUmask(){
		return $this->umask;
	}
	public function setUmask($value){
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
	public function setAllowedDevIPs(array $value = []){
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
	public function run($opts = []){
		if($this->isCli()){
			return $this->runConsole($opts);
		}else{
			return $this->runWeb($opts);
		}
	}
	public function __invoke(array $opts = []){
		return $this->run($opts);
	}

	/*
	Method: runConsole
	Run console application
	*/
	public function runConsole(){
		set_time_limit(0);
		$input = new ArgvInput();
		$envArg = $input->getParameterOption(['--env', '-e'], null);
		if(isset($envArg)){
			$this->setEnvironment($envArg);
		}
		if($this->getDebug() && $input->hasParameterOption(['--no-debug'])){
			$this->setDebug(false);
		}elseif(!$this->getDebug() && $input->hasParameterOption(['--debug'])){
			$this->setDebug(true);
		}
		$this->getConsoleApp()->run($input);
		return $this;
	}

	/*
	Method: runWeb
	Run web application
	*/
	public function runWeb($opts = []){
		if(!$this->isAllowedToRunWeb()){
			header('HTTP/1.0 403 Forbidden');
			exit('You are not allowed to access this file. Check App for more information.');
		}
		if($this->getDebug()){
			$this->enableDebug();
		}
		static::processRequest($this->getKernel(), $opts);
		return $this;
	}

	/*
	Method: processRequest
	Process a request in the standard edition fashion.
	Parameters:
		kernel(KernelInterface): application kernel
		options(Map):
			request(Request): specify an alternative request to process
	*/
	static public function processRequest($kernel, $opts = []){
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
	public function getDebug(){
		if(!isset($this->debug)){
			if(getenv('APP_DEBUG') !== false){
				$this->debug = getenv('APP_DEBUG');
			}elseif(getenv('SYMFONY_DEBUG') !== false){
				$this->debug = getenv('SYMFONY_DEBUG');
			}else{
				$this->debug = ($this->getEnvironment() !== 'prod');
			}
		}
		return $this->debug;
	}
	public function setDebug($debug){
		$this->debug = $debug;
		return $this;
	}
	/*
	Property: environment
	Environment for Symfony kernel.
	*/
	protected $environment;
	public function getEnvironment(){
		if(!isset($this->environment)){
			if(getenv('APP_ENV') !== false){
				$this->environment = getenv('APP_ENV');
			}elseif(getenv('SYMFONY_ENV') !== false){
				$this->environment = getenv('SYMFONY_ENV');
			}elseif(defined(__NAMESPACE__ . '\ENVIRONMENT')){
				$this->environment = constant(__NAMESPACE__ . '\ENVIRONMENT');
			}elseif($this->isCli()){
				$this->environment = 'dev';
			}else{
				$this->environment = 'prod';
			}
		}
		return $this->environment;
	}
	public function setEnvironment($environment){
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
	Name of app, as used for logs, front controller, etc.
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
	protected $paths = [];
	public function addPaths($paths){
		foreach($paths as $name=> $path){
			if(!$this->hasPath($name)){
				$this->setPath($name, $path);
			}
		}
		return $this;
	}
	public function getPath($name){
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
				return $path;
			}else{
				return null;
			}
		}
	}
	public function hasPath($name){
		return (isset($this->paths[$name]));
	}
	protected function mergeInPaths($paths){
		$this->paths = array_merge($this->paths, $paths);
		return $this;
	}
	public function setPath($name, $value){
		$this->paths[$name] = $value;
		return $this;
	}
	public function setPaths($paths){
		$this->paths = $paths;
		return $this;
	}

	/*=====
	==class operation
	=====*/
	static public function callStatic($name, $args = null){
		$instance = static::getSingleton();
		if(method_exists($instance, $name)){
			return call_user_func_array([$instance, $name], $args);
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
	static public function getSingleton($opts = []){
		//-# using 'self' ensures we get an instance of a child class if that is what has been gotten.
		if(self::$singletonInstance === null){
			self::$singletonInstance = new static($opts);
		}elseif(count($opts)){
			throw new \Exception('Singleton already exists.  Cannot pass in options.');
		}
		return self::$singletonInstance;
	}
}
