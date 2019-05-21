<?php
namespace TJM\Bundle\StandardEditionBundle\Component;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use TJM\Bundle\StandardEditionBundle\Component\App\App;
class AppKernel extends Kernel{
	protected $appContainer;
	public function __construct($appContainerOrEnv = null, $debug = null){
		if(!$appContainerOrEnv){
			$appContainerOrEnv = App::getSingleton();
		}elseif(is_string($appContainerOrEnv)){
			//--support symfony kernel interface.  We need a better way to go about this.  This is a stopgap
			$env = $appContainerOrEnv;
			$appContainerOrEnv = App::getSingleton();
			$appContainerOrEnv->setEnvironment($env);
			if($debug){
				$appContainerOrEnv->setDebug($debug);
			}
		}
		$this->appContainer = $appContainerOrEnv;
		parent::__construct($appContainerOrEnv->getEnvironment(), $appContainerOrEnv->getDebug());
	}
	public function getCacheDir(){
		return $this->appContainer->getPath('var') . '/cache/' . $this->getEnvironment();
	}
	protected function getKernelParameters(){
		$params = parent::getKernelParameters();
		$params['tjm.kernel.config_dir'] = $this->appContainer->getPath('config');
		$params['tjm.kernel.var_dir'] = $this->appContainer->getPath('var');
		return $params;
	}
	public function getLogDir(){
		return $this->appContainer->getPath('var') . '/logs';
	}
	public function getProjectDir(){
		return $this->appContainer->getPath('project');
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
