<?php
namespace TJM\Bundle\StandardEditionBundle\Component\App;

use AppKernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use TJM\Bundle\StandardEditionBundle\Component\Config;

class WebApp{
	/*
	Method: run
	Run web application
	*/
	public static function run($options = Array()){
		if(Config::getEnvironment() === 'dev'){
			Debug::enable();
		}

		$kernel = new AppKernel(Config::getEnvironment(), (Config::getDebug()));
		$kernel->loadClassCache();

		if(isset($options['cache']) && $options['cache'] === true){
			//-# untested
			$kernel = new \AppCache($kernel);
		}

		self::processRequest($kernel, $options);

		return $kernel;
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
	public static function processRequest($kernel, $options = Array()){
		if(isset($options['request'])){
			$request = $options['request'];
		}else{
			$request = Request::createFromGlobals();
		}
		$response = $kernel->handle($request);
		$response->send();
		$kernel->terminate($request, $response);
	}
}
