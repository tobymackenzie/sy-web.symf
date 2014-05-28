<?php
namespace TJM\Bundle\StandardEditionBundle\Component\App;

use AppKernel;
use Symfony\Component\Debug\Debug;
use TJM\Bundle\StandardEditionBundle\Component\Config;

class WebApp{
	public static function run(){
		if(Config::getEnvironment() === 'dev'){
			Debug::enable();
		}

		$kernel = new AppKernel(Config::getEnvironment(), (Config::getDebug()));
		$kernel->processRequest();

		return $kernel;
	}
}
