<?php
namespace TJM\SyWeb;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use TJM\SyWeb\App;
class AppKernel extends Kernel{
	protected $app;
	public function __construct($appOrEnv = null, $debug = null){
		if(!$appOrEnv){
			$appOrEnv = App::getSingleton();
		}elseif(is_string($appOrEnv)){
			//--support symfony kernel interface.  We need a better way to go about this.  This is a stopgap
			$env = $appOrEnv;
			$appOrEnv = App::getSingleton();
			$appOrEnv->setEnvironment($env);
			if($debug){
				$appOrEnv->setDebug($debug);
			}
		}
		$this->app = $appOrEnv;
		parent::__construct($appOrEnv->getEnvironment(), $appOrEnv->getDebug());
	}
	public function getCacheDir() :string{
		return $this->app->getPath('cache.' . $this->getEnvironment());
	}
	protected function getKernelParameters() :array{
		$params = parent::getKernelParameters();
		$params['tjm.kernel.config_dir'] = $this->app->getPath('config');
		$params['tjm.kernel.var_dir'] = $this->app->getPath('var');
		return $params;
	}
	public function getLogDir() :string{
		return $this->app->getPath('logs');
	}
	public function getProjectDir() :string{
		return $this->app->getPath('project');
	}
	public function getRootDir(){
		if(!isset($this->rootDir)){
			$this->rootDir = $this->app->getPath('app');
		}
		return $this->rootDir;
	}

	/*=====
	==initialization
	=====*/
	public function registerBundles() :iterable{
		return $this->app->initBundles();
	}

	public function registerContainerConfiguration(LoaderInterface $loader){
		$loader->load($this->app->getConfigPath($this->getEnvironment()));
	}
}
