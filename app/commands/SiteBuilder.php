<?php
namespace App\Commands;

use Collei\Console\CommandLine;
use Collei\Console\Commands\Command;
use Collei\Console\Output\Rich\Formatter;
use Collei\Console\Co;

class SiteBuilder extends Command
{

	protected $signature = 'make:site {name} {topics*}';

	public function handle(CommandLine $com)
	{
		$sub = $com->getSubCommand();

		$texto = $this->ask("Subcommand '{$sub}'. Entre um texto com tags html");

		$args = $this->arguments();

		echo "\r\n\r\n";

		//echo "\r\n- name: " . $args['name'];
		//echo "\r\n- topics: " . implode(', ', $args['topics']);
		echo "\r\n\r\n";

		//$this->write($texto);

		//Co::write(print_r([$com, $args], true), 'red', 'black', ['underline','reversed']);

		echo "\r\n\r\n";
	}

}
