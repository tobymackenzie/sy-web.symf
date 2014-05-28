<?php
namespace TJM\Bundle\StandardEditionBundle\Composer;

// use Composer\Script\CommandEvent;
use Composer\Script\Event;

class ScriptHandler{
	protected static $symfonyStandardPostInstallCommands = Array(
		"Incenteev\\ParameterHandler\\ScriptHandler::buildParameters"
		,"Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap"
		,"Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache"
		,"Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets"
		,"Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile"
	);
	protected static $symfonyStandardPostUpdateCommands = Array(
		"Incenteev\\ParameterHandler\\ScriptHandler::buildParameters"
		,"Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap"
		,"Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache"
		,"Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets"
		,"Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile"
	);
	public static function runSymfonyStandardPostInstallCommands(Event $event){
		self::runCommands(self::$symfonyStandardPostInstallCommands, $event);
	}
	public static function runSymfonyStandardPostUpdateCommands(Event $event){
		self::runCommands(self::$symfonyStandardPostUpdateCommands, $event);
	}
	public static function runCommands($commands, Event $event){
		foreach($commands as $command){
			$event->getComposer()->getEventDispatcher()->dispatchCommandEvent($command, $event->isDevMode());
		}
	}
}
