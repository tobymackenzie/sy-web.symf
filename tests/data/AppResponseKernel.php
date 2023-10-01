<?php
namespace TJM\SyWeb\Tests;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use TJM\SyWeb\AppKernel;

class AppResponseKernel extends AppKernel{
	use MicroKernelTrait;
	public function __construct($appOrEnv = null, $debug = null){
		parent::__construct($appOrEnv, $debug);
	}
	protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader){
		$container->extension('framework', [
			'secret'=> '12345',
		]);
	}
	protected function configureRoutes(RoutingConfigurator $routes){
		$routes->add('home', '/')
			->controller([self::class, 'homeAction'])
		;
	}

	//--override `MicroKernelTrait::registerBundles()` back to parent
	public function registerBundles(): iterable{
		return parent::registerBundles();
	}

	public function homeAction(){
		return new Response('foo');
	}
}
