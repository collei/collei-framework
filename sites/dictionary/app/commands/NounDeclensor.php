<?php

namespace App\Commands;

use Collei\Console\CommandLine;
use Collei\Console\Commands\Command;
use Collei\Console\Output\Rich\Formatter;
use Collei\Console\Co;

/**
 *	This creates a Command and it gets inserted in the Cyno CLI Engine.
 *
 *
 */
class NounDeclensor extends Command
{
	/**
	 *	@var string
	 *
	 *	Define here your command signature
	 */
	protected $signature = "decline {firstarg} {optionalarg?} {--sound} ";

	/**
	 *	@var string
	 *
	 *	Define here your command help (a brief version)
	 */
	protected $help = "Syntax: decline firstarg [optionalarg] [--sound] ";

	/**
	 *	@var string
	 *
	 *	Define here your command help (the long version)
	 */
	protected $longHelp = "Describe here your command.
		<fg=yellow>firstarg</>	the first argument
		<fg=yellow>optionalarg</>	the second argument. It is optional.
		<fg=yellow>--sound</>	the third argument is a flag.
				Flags are always optional.
	";

	/**
	 *	Entry point of your command line
	 *
	 *	@param	CommandLine	$com
	 *	@return	mixed
	 */
	public function handle(CommandLine $com)
	{
		$this->write("Command <fg=yellow>decline</> not implemented.");
	}
}
