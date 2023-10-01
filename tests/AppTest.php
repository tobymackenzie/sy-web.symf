<?php
namespace TJM\SyWeb\Tests;
use TJM\SyWeb\App;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase{
	public function testDebug(){
		$app = new App();
		$this->assertEquals($app->getDebug(), false);
		$app = new App();
		$app->setEnvironment('dev');
		$this->assertEquals($app->getDebug(), true);
	}
	public function testEnvironment(){
		$app = new App();
		$this->assertEquals($app->getEnvironment(), 'prod');
		$app->setEnvironment('dev');
		$this->assertEquals($app->getEnvironment(), 'dev');
	}
	public function testPaths(){
		$app = new App();
		$this->assertEquals(realpath(__DIR__ . '/..'), realpath($app->getPath('project')));
		$this->assertEquals(realpath(__DIR__ . '/../src'), realpath($app->getPath('src')));
		$this->assertEquals(realpath(__DIR__ . '/../vendor'), realpath($app->getPath('vendor')));
	}
}
