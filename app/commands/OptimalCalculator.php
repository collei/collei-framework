<?php
namespace App\Commands;

use Collei\Console\CommandLine;
use Collei\Console\Commands\Command;
use Collei\Console\Output\Rich\Formatter;
use Collei\Console\Co;

class OptimalCalculator extends Command
{
	protected $signature = "opcalc {pieces*}";

	public function handle(CommandLine $com)
	{
		echo "Not implemented.";
	}
}

