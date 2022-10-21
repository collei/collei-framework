<?php
namespace Collei\Console;

use Collei\Console\CommandLine;
use Collei\Console\ConsoleServletDispatcher;
use Collei\Console\Commands\Command;
use Collei\Console\Commands\CommandEntries;
use Collei\Console\Co;
use Collei\Support\Runnable\Runnable;
use Collei\Support\Str;

/**
 *	Encapsulates the console application running instance
 *
 *	@author alarido <alarido.su@gmail.com>	
 *	@since 2021-07
 */
class ConsoleApp implements Runnable
{
	/**
	 *	Constants
	 */
	public const CA_MODE_NONE = 0;
	public const CA_MODE_SILENT = 1;

	/**
	 *	@var \Collei\Console\CommandLine $commandLine
	 */
	private $commandLine;

	/**
	 *	@var \Collei\Console\Environment $environment
	 */
	private $environment;

	/**
	 *	@var \Collei\Console\CommandLine $result
	 */
	private $result;

	/**
	 *	@var int
	 */
	private $flags = self::CA_MODE_NONE;

	/**
	 *	@var array $COMMAND_CLASSES_DIRLIST
	 */
	private $COMMAND_CLASSES_DIRLIST = [
		'app/console/commands',
	];

	/**
	 *	@var array $COMMAND_CLASSES_LIST
	 */
	private $COMMAND_CLASSES_LIST = [];

	/**
	 *	Initializes the application and related dependencies
	 */
	private function init()
	{
		$this->seeker();
		$this->siteSeeker();
		$this->seekPlatformInfo();
		$this->requirer();
	}

	/**
	 *	Scan the directory for class dependencies
	 *
	 *	@param	string	$dir	the directory to be scanned
	 *	@return	void
	 */
	private function seeker(string $dir = null)
	{
		$dir = $dir ?? PLAT_GROUND . DIRECTORY_SEPARATOR . PLAT_APP_COMMANDS_FOLDER;
		//
		if (!is_dir($dir)) {
			return false;
		}
		//
		$files = scandir($dir);
		//
		if ($files === false) {
			return false;
		}
		//
		$files = array_diff($files, ['..','.']);
		//
		foreach ($files as $file) {
			if (Str::endsWith($file, PLAT_CLASSES_SUFFIX)) {
				$class = str_replace(PLAT_CLASSES_SUFFIX, '', $file);
				//
				$this->COMMAND_CLASSES_LIST[] = [
					'file' => $dir . DIRECTORY_SEPARATOR . $file,
					'class' => PLAT_SITES_COMMANDS_FOLDER . DIRECTORY_SEPARATOR . $class
				];
			}
		}
	}

	/**
	 *	Seeks for directories to be scanned and scan them
	 *
	 *	@return	void
	 */
	private function siteSeeker()
	{
		$sitebase = PLAT_SITES_GROUND;
		//
		if (!is_dir($sitebase)) {
			return false;
		}
		//
		$sites = scandir($sitebase);
		//
		if ($sites === false) {
			return false;
		}
		//
		$sites = array_diff($sites, ['..','.']);
		//
		foreach ($sites as $site) {
			$dir = $sitebase . DIRECTORY_SEPARATOR . $site . DIRECTORY_SEPARATOR . PLAT_SITES_COMMANDS_FOLDER;
			//
			$this->seeker($dir);
		}
	}

	/**
	 *	Requires all the scanned dependencies
	 *
	 *	@return	void
	 */
	private function requirer()
	{
		require_once '../app/console.php';
		//
		foreach ($this->COMMAND_CLASSES_LIST as $item) {
			require_once $item['file'];
			//
			$class = $item['class'];
			$t = new $class();
		}
	}

	/**
	 *	Seeks for site and platform info
	 *
	 *	@return	void
	 */
	private function seekPlatformInfo()
	{
		$sites = dir_lis(PLAT_SITES_GROUND);
		$info = [];
		//
		foreach ($sites as $site)
		{
			$piece = [
				'name' => $site,
				'url' => PLAT_SITES_BASEURL . '/' . $site,
				'ground' => PLAT_SITES_GROUND . DIRECTORY_SEPARATOR . $site,
			];
			//
			$info[$site] = $piece;
		}
		//
		self::$siteStructs = $info;
	}
	
	/**
	 *	Builds and initializes a new console app instance
	 *
	 *	@param	\Collei\Console\CommandLine	Command line arguments	
	 */
	private function __construct(
		CommandLine $commandLine, int $flags = self::CA_MODE_NONE
	) {
		self::$instance = $this;
		//
		$this->environment = new Environment('.cynoe');
		$this->flags = $flags;
		$this->init();
		$this->commandLine = $commandLine;
	}

	/**
	 *	Performs command dispatching
	 *
	 *	@param	\Collei\Console\Commands\Command	the console servlet instance	
	 *	@param	\Collei\Console\CommandLine			command line arguments	
	 *	@return	void
	 */
	private function dispatch(Command $command, CommandLine $commandLine)
	{
		$this->result = (new ConsoleServletDispatcher($this))
				->dispatch($command, $commandLine);
	}

	/**
	 *	perform searching and matching of sent command line
	 *	and runs the corresponding servlet, if found
	 *
	 *	@return	void
	 */
	private function processCommands()
	{
		if (($com = CommandEntries::find($this->commandLine)) !== false) {
			return $this->dispatch($com, $this->commandLine);
		} else {
			$message = ' No command found to match the passed command'
				. ' and arguments ! ';
			//
			Co::newLine();
			Co::write(" [ERROR] ", 'red', 'white');
			Co::write($message, 'yellow', 'black');
			Co::newLine();
			//
			return -1;
		}
	}

	/**
	 *	Starts running the application instance
	 *
	 *	@return	void
	 */
	public function run()
	{
		return $this->processCommands();
	}


	/**
	 *	@var bool $silent
	 */
	public final function __get($name)
	{
		if ($name == 'silent') {
			return $this->flags & self::CA_MODE_SILENT;
		}
	}

	/**
	 *	Sets an environment variable
	 *
	 *	@param	string	$name
	 *	@param	mixed	$value		
	 *	@return	void
	 */
	public function setEnv(string $name, $value)
	{
		$this->environment->set($name, $value);
	}

	/**
	 *	Gets the environment variable, if any
	 *
	 *	@param	string	$name
	 *	@return	string|null
	 */
	public function getEnv(string $name)
	{
		return $this->environment->get($name);
	}

	/**
	 *	Lists all environment variable names, if any
	 *
	 *	@return	array
	 */
	public function listEnv()
	{
		return $this->environment->names();
	}

	/**
	 *	Lists all environment variable names, if any
	 *
	 *	@return	array
	 */
	public function listEnvWithValues()
	{
		$arr = [];
		$names = $this->environment->names();
		//
		foreach ($names as $name) {
			$arr[$name] = $this->environment->get($name);
		}
		//
		return $arr;
	}

	/**
	 *	@var \Collei\Console\ConsoleApp $instance
	 */
	private static $instance;

	/**
	 *	@var array $siteStructs
	 */
	private static $siteStructs = [];

	/**
	 *	Builds, initializes and starts a new console app instance
	 *
	 *	@param	\Collei\Console\CommandLine	Command line arguments	
	 *	@return	\Collei\Console\ConsoleApp		
	 */
	public static function start(CommandLine $commandLine, int $flags = self::CA_MODE_NONE)
	{
		if (!self::isInConsoleMode()) {
			logerror(
				'Invalid environment',
				'Attempt to launch collei CLI under WEB environment.'
			);
			return false;
		}

		return new self($commandLine, $flags);
	}

	/**
	 *	Gets the console app current instance
	 *
	 *	@return	\Collei\Console\ConsoleApp		
	 */
	public static function getInstance()
	{
		return self::$instance;
	}

	/**
	 *	Returns basic info on the give site
	 *
	 *	@param	string	$site	site shortname (site folder name)
	 *	@return	array|null
	 */
	public static function siteInfo(string $site)
	{
		return static::$siteStructs[$site] ?? null;
	}

	/**
	 *	Detects whether the application is running under PHP CLI
	 *	or as web server
	 *
	 *	@author Silver Moon <https://www.binarytides.com/author/admin/>
	 *	@since 2020-07-30
	 *	@source <https://www.binarytides.com/php-check-running-cli/>
	 *			viewed 2021-11-17 20:28 GMT-3
	 */
	public static function isInConsoleMode()
	{
		if (defined('STDIN')) {
			return true;
		}
		//
		if (
			empty($_SERVER['REMOTE_ADDR'])
			and !isset($_SERVER['HTTP_USER_AGENT'])
			and (count($_SERVER['argv']) > 0)
		) {
			return true;
		} 
		//
		return false;
	}
}


