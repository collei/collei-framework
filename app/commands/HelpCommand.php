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
class HelpCommand extends Command
{
	/**
	 *	@var string
	 *
	 *	Define here your command signature
	 */
	protected $signature = "help {commandName} {--full} ";

	/**
	 *	@var string
	 *
	 *	Define here your command help (a brief version)
	 */
	protected $help = "<fg=light-blue>Syntax: help <command-name> [--full] </>\r\n";

	/**
	 *	@var string
	 *
	 *	Define here your command help (the long version)
	 */
	protected $longHelp = ''
		. "<fg=light-blue>Show help and detailed info about a command.</>\r\n\n"
		. "	<fg=yellow><command-name></>	a command name\r\n"
		. "	<fg=yellow>--full</>		shows more detailed info.\r\n"
		. "			Omit --full for just a brief description.\r\n";

	/**
	 *	Entry point of your command line
	 *
	 *	@param	CommandLine	$com
	 *	@return	mixed
	 */
	public function handle(CommandLine $com)
	{
		$commandName = $this->argument('commandName');
		$brief = $this->option('full');
		//
		if ($item = $this->findCommand($commandName)) {
			$item->displayHelp($brief === true);
		} else {
			$this->write("Command <fg=yellow>$commandName</> not found.\r\n");
		}
	}
}
