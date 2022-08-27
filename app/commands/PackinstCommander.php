<?php
namespace App\Commands;

use Collei\Console\CommandLine;
use Collei\Console\Commands\Command;
use Collei\Console\Output\Rich\Formatter;
use Collei\Console\Co;

use Packinst\Package\PackageManager;
use Packinst\Package\GithubPackage;

class PackinstCommander extends Command
{
	protected $signature = 'packet
							{command : a valid command (either list, add, update, remove)}
							{args? : name of the package in format vendor/project }';

	private $plugin_list;

	protected function echoIt(...$event)
	{
		$this->info(implode('', $event ?? ['@emptyEvent']));
	}

	protected function init()
	{
		PackageManager::setLocation(PLAT_VENDOR_GROUND);
		//
		$logger = [$this, 'echoIt'];
		PackageManager::setLogListener(function (...$event) use ($logger) {
			$logger(...$event);
		});
		//
		$this->plugin_list = PackageManager::getInstalledPackages(true);
	}

	private function install()
	{
		if ($package = $this->argument('args')) {
			$git = new GithubPackage($package);
			$git->fetchRepositoryInfo();
			//
			if ($git->repositoryExists()) {
				if (!PackageManager::install($git, true)) {
					$this->warn("Could not install $package - please verify.");
				}
			} else {
				$this->warn("Package $package not found on Github, please verify.");
			}
		} else {
			$this->warn("No package specified.");
		}
	}

	private function uninstall()
	{
		if ($package = $this->argument('args')) {
			if (!PackageManager::remove($package)) {
				$this->warn("Unremovible: Could not remove $package - please verify.");
			}
		} else {
			$this->warn("No package specified.");
		}
	}

	private function update()
	{
		if ($package = $this->argument('args')) {
			if (!PackageManager::update($package)) {
				$this->warn("Update of $package was not successful - please verify.");
			}
		} else {
			$this->warn("No package specified.");
		}
	}

	private function enlist()
	{
		foreach ($this->plugin_list as $n => $v) {
			echo "- $n\r\n";
		}
	}

	public function handle(CommandLine $com)
	{
		$option = $this->argument('command', 'pluginer');
		$more = $this->argument('args', 'list');
		//
		if ($option == 'add') {
			$this->install();
		} elseif ($option == 'remove') {
			$this->uninstall();
		} elseif ($option == 'update') {
			$this->update();
		} elseif ($option == 'list') {
			$this->enlist();
		} else {
			$this->write("There is no such option: <fg=yellow>$option</>\r\n;");
		}
	}

}
