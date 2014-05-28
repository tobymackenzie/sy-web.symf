<?php
namespace TJM\Bundle\StandardEditionBundle\Component\App;

use AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use TJM\Bundle\StandardEditionBundle\Component\Config;

class ConsoleApp{
	public static function run(){
		set_time_limit(0);

		$input = new ArgvInput();
		Config::setEnvironment($input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ?: 'dev'));
		if(Config::getEnvironment() !== 'prod'){
			Config::setDebug(getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(array('--no-debug', '')));
		}else{
			Config::setDebug(getenv('SYMFONY_DEBUG') !== '0' && $input->hasParameterOption(array('--debug', '')));
		}

		$kernel = new AppKernel(Config::getEnvironment(), Config::getDebug());
		$application = new Application($kernel);
		$application->run($input);

		return $application;
	}
}
