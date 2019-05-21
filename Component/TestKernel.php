<?php
/*
class: TestKernel
Use for phpunit unit tests with 'symfony/phpunit-bridge'.  Required because the bridge instantiates a class from string, and we need to get our instance its `App`.
*/
namespace TJM\Bundle\StandardEditionBundle\Component;
use TJM\Bundle\StandardEditionBundle\Component\App\App;
class TestKernel extends AppKernel{
	public function __construct($env = null, $debug = null){
		global $app;
		if(isset($app) && $app instanceof App){
			$app->setEnvironment($env);
			$app->setDebug($debug);
			parent::__construct($app);
		}else{
			parent::__construct($env, $debug);
		}
	}
}
