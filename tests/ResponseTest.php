<?php
namespace TJM\SyWeb\Tests;
use TJM\SyWeb\App;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/data/AppResponseKernel.php');
class ResponseTest extends TestCase{
	static protected $projectPath = __DIR__ . '/tmp';
	protected $app;
	public function setUp(): void{
		chdir(__DIR__);
		mkdir(self::$projectPath);
		$this->app = new App();
		$this->app
			->setKernelClass(AppResponseKernel::class)
			->setPath('project', self::$projectPath)
		;
	}
	public function tearDown(): void{
		passthru('rm -r ' . __DIR__ . '/tmp*');
	}
	public function testHomeResponse(){
		$response = $this->app->getResponse('/');
		$this->assertEquals('foo', $response->getContent());
		$this->assertEquals(200, $response->getStatusCode());
	}
	public function testNotFound(){
		//-! shows error log, not sure how to get rid of that
		$response = $this->app->getResponse('/404');
		$this->assertStringContainsStringIgnoringCase('Not Found', $response->getContent());
		$this->assertEquals(404, $response->getStatusCode());
	}
}
