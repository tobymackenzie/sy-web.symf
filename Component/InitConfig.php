<?php
/*
Class: InitConfig
Class for storing initial config for helping with things that happen before Symfony is initialized.  Meant to be defined in Symfony-Initial, but here just in case it isn't.  Meant to be subclassed by Config.
*/
namespace TJM\Bundle\StandardEditionBundle\Component;

class InitConfig{
	/*
	Property: paths
	Collection of paths to be used in early lifecycle
	*/
	public static $paths = Array();
}
