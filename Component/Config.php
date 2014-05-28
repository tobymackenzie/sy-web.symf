<?php
/*
Class: Config
Class for storing initial config for helping with things that happen before Symfony is initialized.  Meant to be accessed statically so it leaves no global trace and acts as a singleton.
*/
namespace TJM\Bundle\StandardEditionBundle\Component;

class Config extends InitConfig{
	/*
	Property: debug
	Whether to enable Symfony debugging or not.
	*/
	public static $debug;
	public static function getDebug(){
		if(!isset(self::$debug)){
			self::$debug = (self::getEnvironment() !== 'prod');
		}
		return self::$debug;
	}
	public static function setDebug($debug){
		self::$debug = $debug;
	}

	/*
	Property: environment
	Environment for Symfony kernel.
	*/
	public static $environment = 'prod';
	public static function getEnvironment(){
		return self::$environment;
	}
	public static function setEnvironment($environment){
		self::$environment = $environment;
	}

	/*
	Property: isCli
	Whether app is being run as a CLI application
	*/
	public static $isCli;
	public static function isCli(){
		if(!isset(self::$isCli)){
			self::$isCli = (php_sapi_name() == 'cli');
		}
		return self::$isCli;
	}

	/*
	Property: paths
	Collection of paths to be used in early lifecycle.  Defined in InitConfig
	*/
	public static function getPath($name){
		return (self::hasPath($name)) ? self::$paths[$name] : null;
	}
	public static function hasPath($name){
		return (isset(self::$paths[$name]));
	}
	public static function setPath($name, $value){
		self::$paths[$name] = $value;
	}
	public static function setPaths($paths){
		self::$paths = array_merge(self::$paths, $paths);
	}
	public static function setPathDefaults(){
		if(!isset(self::$paths['app'])){
			self::$paths['app'] = (self::isCli())
				? exec('pwd') . '/app'
				: $_SERVER['DOCUMENT_ROOT'].'/../app'
			;
		}
		if(!isset(self::$paths['PHPCLI'])){
			self::$paths['PHPCLI'] = "/usr/bin/env php";
		}
		if(!isset(self::$paths['src'])){
			self::$paths['src'] = self::$paths['app']."/../src";
		}
		if(!isset(self::$paths['vendor'])){
			self::$paths['vendor'] = self::$paths['app']."/../vendor";
		}
		if(!isset(self::$paths['tjmSEBundle'])){
			self::$paths['tjmSEBundle'] = __DIR__ . '/../Resources';
		}
	}
}
