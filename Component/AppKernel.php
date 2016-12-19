<?php
namespace TJM\Bundle\StandardEditionBundle\Component;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use TJM\Bundle\StandardEditionBundle\Component\App\App;
class AppKernel extends Kernel{
	protected $appContainer;
	public function __construct(App $appContainer = null){
		if(!$appContainer){
			$appContainer = App::getSingleton();
		}
		$this->appContainer = $appContainer;
		parent::__construct($appContainer->getEnvironment(), $appContainer->getDebug());
	}
	public function getCacheDir(){
		return dirname($this->getRootDir()) . '/var/cache/' . $this->getEnvironment();
	}
	public function getLogDir(){
		return dirname($this->getRootDir()) . '/var/logs';
	}
	public function getRootDir(){
		if(!isset($this->rootDir)){
			$this->rootDir = $this->appContainer->getPath('app');
		}
		return $this->rootDir;
	}

	/*=====
	==initialization
	=====*/
	public function registerBundles(){
		return $this->appContainer->initBundles();
	}

	public function registerContainerConfiguration(LoaderInterface $loader){
		$loader->load($this->appContainer->getConfigPath($this->getEnvironment()));
	}
}
