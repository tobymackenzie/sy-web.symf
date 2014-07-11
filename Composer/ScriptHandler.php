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
		//--merge 'extra' from this package into root 'extra' before passing on to other commands
		$thisExtra = null;
		foreach($event->getComposer()->getRepositoryManager()->getLocalRepository()->findPackages('tjm/symfony-standard-edition-bundle') as $package){
			if($package instanceof \Composer\Package\CompletePackage){
				$thisExtra = $package->getExtra();
				break;
			}
		}
		$rootPackage = $event->getComposer()->getPackage();
		$rootExtra = $rootPackage->getExtra();
		$extra = array_merge($thisExtra, $rootExtra);
		$rootPackage->setExtra($extra);

		//--call commands from Symfony Standard Edition
		foreach($commands as $command){
			forward_static_call($command, $event);
		}
	}
}
