<?php
namespace TJM\SyWeb\Tests;
use TJM\SyWeb\App;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase{
	public function testDebug(){
		$app = new App(['isCli'=> false]);
		$this->assertEquals(false, $app->getDebug());
		$app = new App(['isCli'=> false]);
		$app->setEnvironment('dev');
		$this->assertEquals(true, $app->getDebug());
	}
	public function testEnvironment(){
		$app = new App(['isCli'=> false]);
		$this->assertEquals('prod', $app->getEnvironment());
		$app->setEnvironment('dev');
		$this->assertEquals('dev', $app->getEnvironment());
	}
	public function testPaths(){
		$app = new App();
		$this->assertEquals(realpath(__DIR__ . '/..'), realpath($app->getPath('project')));
		$this->assertEquals(realpath(__DIR__ . '/../src'), realpath($app->getPath('src')));
		$this->assertEquals(realpath(__DIR__ . '/../vendor'), realpath($app->getPath('vendor')));
	}
}
