<?php
namespace App\Commands;

use Collei\Console\CommandLine;
use Collei\Console\Commands\Command;
use Collei\Console\Output\Rich\Formatter;
use Collei\Console\Co;

use Collei\Utils\Files\TextFile;

use App\Services\SiteEngineService;

class CommandMaker extends Command
{
	private $service;

	protected $signature = 'make:command {class-name} {command-name} {site=plat}';

	public function handle(CommandLine $com, SiteEngineService $service)
	{
		$this->service = $service;
		$className = $this->argument('class-name');
		$commandName = $this->argument('command-name', 'mycommand');
		$site = $this->argument('site', 'plat');
		//
		if (
			$this->service->createFile(
				$className,
				$site,
				PLAT_COMMANDS_FOLDER_NAME,
				['commandName' => $commandName]
			)
		) {
			$this->info("- Comando $commandName (classe $className) criado com sucesso em $site. ");
		} else {
			$this->warn("- Algum erro ocorrido ao criar $commandName ($className) em $site. ");
		}
	}

}

