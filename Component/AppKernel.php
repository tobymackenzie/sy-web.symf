<?php
namespace TJM\Bundle\StandardEditionBundle\Component;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use TJM\Bundle\StandardEditionBundle\Component\App\App;
class AppKernel extends Kernel{
	public function getCacheDir(){
		return App::getPath('var') . '/cache/' . $this->getEnvironment();
	}
	public function getLogDir(){
		return App::getPath('var') . '/logs';
	}
	public function getRootDir(){
		if(!isset($this->rootDir)){
			$this->rootDir = App::getPath('app');
		}
		return $this->rootDir;
	}

	/*=====
	==initialization
	=====*/
	public function registerBundles(){
		return App::initBundles();
	}

	public function registerContainerConfiguration(LoaderInterface $loader){
		$loader->load(App::getConfigPath($this->getEnvironment()));
	}
}
