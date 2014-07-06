<?php
namespace TJM\Bundle\StandardEditionBundle\Component;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
// use TJM\StandardEditionBundle\Component\Config;

class AppKernel extends Kernel{
	/*=====
	==initialization
	=====*/
	public function registerBundles(){
		$bundles = array(
			//--standard
			//---framework
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle()
			//---standard symfony
			,new \Symfony\Bundle\SecurityBundle\SecurityBundle()
			,new \Symfony\Bundle\TwigBundle\TwigBundle()
			,new \Symfony\Bundle\MonologBundle\MonologBundle()
			,new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle()
			,new \Symfony\Bundle\AsseticBundle\AsseticBundle()
			,new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle()
			,new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle()
			//--this
			,new \TJM\Bundle\StandardEditionBundle\TJMStandardEditionBundle()
		);

		if (in_array($this->getEnvironment(), array('dev', 'test'))) {
			$bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
			$bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
			$bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
		}

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader){
		$loader->load(Config::getPath('app') . '/config/config_' . $this->getEnvironment() . '.yml');
	}
}
