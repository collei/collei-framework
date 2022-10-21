<?php
namespace Collei\Console\Commands;

use Collei\Console\Co;
use Collei\Console\ConsoleServlet;
use Collei\Console\CommandLine;
use Collei\Console\ConsoleApp;
use Collei\Console\Commands\CommandEntries;
use Collei\Console\Output\OutputStyler;
use Collei\Console\Output\Rich\Formatter;
use Collei\Console\CynoConsoleException;
use Collei\Support\Str;
use Collei\Support\Geometry\Rect;
use Closure;

/**
 *	Encapsulates the basic ground of a command
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-08-xx
 */
class Command extends ConsoleServlet
{
	/**
	 *	@var \Collei\Console\ConsoleApp
	 */
	private $app;

	/**
	 *	@var \Collei\Console\Output\OutputStyler
	 */
	private $output;

	/**
	 *	@var array
	 */
	private $arguments = [];

	/**
	 *	@var array
	 */
	private $options = [];

	/**
	 *	@var string
	 */
	private $regex = '';

	/**
	 *	@var string
	 */
	private $commandName = '';

	/**
	 *	@var string
	 */
	private $commandSubName = '';

	/**
	 *	@var string
	 */
	private $commandLineString = '';

	/**
	 *	@var array
	 */
	private $parameterInfo = [];

	/**
	 *	@var string
	 */
	protected $signature = '';

	/**
	 *	@var string
	 */
	protected $description = '';

	/**
	 *	@var string
	 */
	protected $help = '';

	/**
	 *	@var string
	 */
	protected $longHelp = '';

	/**
	 *	translates the signature into a regex for matching
	 *
	 *	@return	void
	 */
	protected final function compileRegexFromSignature()
	{
		$info = [];
		//
		if (!empty($this->signature)) {
			if (CommandEntries::parseSignature($this->signature, $info)) {
				$this->parameterInfo = $info;
				$patt = '#^([^\\s\\:]+)(\\:([^\\s]+))?#i';
				$data = [];
				//
				if (preg_match($patt, ltrim($this->signature), $data)) {
					$this->commandName = $data[1];
					$this->commandSubName = $data[3] ?? '';
				}
			} else {
				throw new CynoConsoleException(
					'Command signature is malformed or the syntax is invalid.'
				);
			}
		}
	}

	/**
	 *	parse the command arguments in their constituent parts and
	 *	and returns info on each single one.
	 *
	 *	@param	string $input
	 *	@return	array
	 */
	protected final function scanArgumentLiterals(string $input)
	{
		$argList = Str::parseArguments($input);
		array_shift($argList);
		$argInfo = [];
		$regex = '#^((-(\w)|--(\w[\w\-:]*\w))(=(.*)|)?|(.*))$#i';
		//
		foreach ($argList as $k => $item) {
			$data = [];
			//
			if (preg_match($regex, $item, $data)) {
				$op_shortcut = ($data[3] ?? '');
				$op_name = ($data[4] ?? '');
				$op_value = ($data[6] ?? '');
				$arg_value = ($data[7] ?? '');
				//
				if (!empty($arg_value)) {
					$argInfo[] = [
						'index' => $k,
						'type' => 'ARG',
						'value' => $arg_value,
					];
				} else {
					$argInfo[] = [
						'index' => $k,
						'type' => 'OPT',
						'name' => $op_name,
						'shortcut' => $op_shortcut,
						'value' => (empty($op_value) ? true : $op_value),
					];
				}
			}
		}
		//
		return $argInfo;
	}

	/**
	 *	Process all arguments contained in the $realParams array
	 *
	 *	@param	array $realParams
	 *	@return	void
	 */
	protected final function processArguments(array $realParams)
	{
		$argCount = count($realParams);
		$lastIndex = 0;
		//
		foreach ($this->parameterInfo as $info) if ($info['type'] == 'ARG') {
			$p_name = $info['name'];
			//
			for ($i=$lastIndex; $i < $argCount; $i++) {
				$item = $realParams[$i];
				//
				if ($item['type'] != 'ARG') {
					continue;
				}
				//
				if ($info['isArray']) {
					if (array_key_exists($p_name, $this->arguments)) {
						$this->arguments[$p_name][] = $item['value'];
					} else {
						$this->arguments[$p_name] = [
							$item['value']
						];
					}
				} else {
					$this->arguments[$p_name] = $item['value'];
					//
					$lastIndex = $i + 1;
					break;
				}
			}
		}
	}

	/**
	 *	Process all options contained in the $realParams array
	 *
	 *	@param	array $realParams
	 *	@return	void
	 */
	protected final function processOptions(array $realParams)
	{
		$argCount = count($realParams);
		$lastIndex = 0;
		//
		foreach ($this->parameterInfo as $info) if ($info['type'] == 'OPT') {
			$p_name = $info['name'];
			//	
			for ($i=$lastIndex; $i < $argCount; $i++) {
				$item = $realParams[$i];
				//
				if ($item['type'] != 'OPT') {
					continue;
				}
				//
				if (
					($item['name'] == $p_name) ||
					($item['shortcut'] == $info['shortcut'])
				) {
					if ($info['isArray']) {
						if (array_key_exists($p_name, $this->options)) {
							$this->options[$p_name][] = $item['value'];
						} else {
							$this->options[$p_name] = [
								$item['value']
							];
						}
					} else {
						$this->options[$p_name] = $item['value'];
						//
						$lastIndex = $i + 1;
						break;
					}
				}
			}
		}
	}

	/**
	 *	parse the command line and assign the values to their
	 *	corresponding names
	 *
	 *	@param	\Collei\Console\CommandLine $commandLine
	 *	@return	void
	 */
	protected final function setArguments(CommandLine $commandLine)
	{
		$line = ($this->commandLineString = (string)$commandLine);
		$realParams = $this->scanArgumentLiterals($line);
		//
		$this->processArguments($realParams);
		$this->processOptions($realParams);
	}

	/**
	 *	Initializes a new instance of the underlying extender class
	 *
	 */
	public final function __construct()
	{
		$this->app = ConsoleApp::getInstance();
		//
		CommandEntries::add($this->signature, $this);
		//
		$this->output = OutputStyler::build([
			'line'		=> '<fg=white;bg=default>',
			'info'		=> '<fg=green;bg=default>',
			'comment'	=> '<fg=yellow;bg=default>',
			'error'		=> '<fg=white;bg=red>',
			'question'	=> '<fg=cyan;bg=default>',
			'warn'		=> '<fg=yellow;bg=blue>',
		]);
		//
		$this->compileRegexFromSignature();
		$this->init();
	}

	/**
	 *	Class finalizer
	 *
	 *	@return	void
	 */
	public final function __destruct()
	{
		$this->term();
	}

	/**
	 *	Class finalizer
	 *
	 *	@return	void
	 */
	public final function __debugInfo()
	{
		return [
			'name:private' => $this->commandName,
			'description:protected' => $this->description,
			'signature:protected' => $this->signature,
			'parameterInfo:private' => $this->parameterInfo,
			'commandLine:private' => $this->commandLineString,
			'arguments:private' => $this->arguments,
			'options:private' => $this->options,
		];
	}

	/**
	 *	Returns the command name
	 *	(e.g.,	for "dothis" returns "dothis",
	 *			for "perform:xpto" returns "perform:xpto")
	 *
	 *	@return	string
	 */
	public final function getName()
	{
		return $this->commandName . (
			$this->commandSubName ? (':' . $this->commandSubName) : ''
		);
	}

	/**
	 *	Returns the command subname 
	 *	(e.g.,	for "dothis" returns "",
	 *			for "perform:xpto" returns "xpto")
	 *
	 *	@return	string
	 */
	public final function getSubName()
	{
		return $this->commandSubName;
	}

	/**
	 *	Returns the command line typed by the user
	 *	(excluding tyhe CLI handler part)
	 *
	 *	@return	string
	 */
	public final function getCommandLine()
	{
		return $this->commandLineString;
	}

	/**
	 *	Enables or disables silent mode (output supression)
	 *
	 *	@param	bool	$silent
	 *	@return	void
	 */
	public final function setSilent(bool $silent)
	{
		parent::setSilent($silent);
		//
		$this->output->setSilent($silent);
	}

	/**
	 *	Returns whether the silent mode is enabled
	 *
	 *	@return	bool
	 */
	public final function silent()
	{
		return parent::silent();
	}

	/**
	 *	Diplays help info on the command
	 *
	 *	@param	string	$longHelp
	 *	@return	void
	 */
	public final function displayHelp(bool $longHelp = false)
	{
		$for = 'is available for <fg=light-green>' . $this->commandName;
		$whenEmpty = "<fg=yellow>No description $for<fg=yellow>.</>\r\n";
		$whenLongEmpty = "<fg=yellow>No help text $for<fg=yellow>.</>\r\n";
		//
		$content = $longHelp 
			? (empty($this->longHelp) ? $whenLongEmpty : $this->longHelp)
			: (empty($this->help) ? $whenEmpty : $this->help);
		//
		$this->write($content);
	}

	/**
	 *	Finds a command and returns its instance, if any
	 *
	 *	@param	string	$command	the command name
	 *	@return	(instanceof Command)|false
	 */
	public final function findCommand(string $commandName)
	{
		return CommandEntries::findByName($commandName);
	}

	/**
	 *	Retrieves an argument value by its name - if it exists
	 *
	 *	@param	string	$name
	 *	@return	string|null
	 */
	public function argument(string $name, $default = null)
	{
		if (isset($this->arguments[$name])) {
			return $this->arguments[$name];
		}
		//
		foreach ($this->parameterInfo as $param) if ($param['type'] == 'ARG') {
			if ($param['name'] == $name) {
				return $param['defaultValue'] ?? $default;
			}
		}
		//
		return $default;
	}

	/**
	 *	Retrieves the number of values under an argument name
	 *
	 *	@param	string	$name
	 *	@return	int
	 */
	public function argumentCount(string $name)
	{
		if (is_array($this->arguments[$name])) {
			return count($this->arguments[$name]);
		}
		//
		return isset($this->arguments[$name]) ? 1 : 0;
	}

	/**
	 *	Retrieves all arguments at once, as array
	 *
	 *	@return	array
	 */
	public function arguments()
	{
		$args = [];
		//
		foreach ($this->parameterInfo as $param) if ($param['type'] == 'ARG') {
			$name = $param['name'];
			$default = $param['defaultValue'];
			$args[$name] = $this->arguments[$name] ?? $default;
		}
		//
		return $args;
	}

	/**
	 *	Retrieves an option value by its name
	 *
	 *	@param	string	$name
	 *	@return	string|bool
	 */
	public function option(string $name, $default = null)
	{
		if (isset($this->options[$name])) {
			return $this->options[$name];
		}
		//
		foreach ($this->parameterInfo as $param) if ($param['type'] == 'OPT') {
			if ($param['name'] == $name) {
				return $param['defaultValue'] ?? $default;
			}
		}
		//
		return $default;
	}

	/**
	 *	Retrieves the number of values under an option name
	 *
	 *	@param	string	$name
	 *	@return	int
	 */
	public function optionCount(string $name)
	{
		if (is_array($this->options[$name])) {
			return count($this->options[$name]);
		}
		//
		return isset($this->options[$name]) ? 1 : 0;
	}

	/**
	 *	Retrieves all options at once, as array
	 *
	 *	@return	array
	 */
	public function options()
	{
		$opts = [];
		//
		foreach ($this->parameterInfo as $param) if ($param['type'] == 'OPT') {
			$name = $param['name'];
			$default = $param['defaultValue'] ?: false;
			$opts[$name] = $this->options[$name] ?? $default;
		}
		//
		return $opts;
	}

	/**
	 *	Calls another command from here
	 *
	 *	@param	string	$command	the command name as from CLI.
	 *	@param	array	$arguments	arguments, if needed
	 *	@return	mixed
	 */
	public function call(string $command, array $arguments = null)
	{
		return $this->invokeAnother($command, $arguments);
	}

	/**
	 *	Calls another command from here, suppressing all its output
	 *
	 *	@param	string	$command	the command name as from CLI.	
	 *	@param	array	$arguments	arguments, if needed
	 *	@return	mixed
	 */
	public function callSilently(string $command, array $arguments = null)
	{
		return $this->invokeAnother(
			$command, $arguments, ConsoleApp::CA_MODE_SILENT
		);
	}

	/**
	 *	Reads a char from the console input. Prompt is optional.s
	 *
	 *	@param	string	$question
	 *	@return	string
	 */
	public function readkey(string $prompt = '')
	{
		return Co::readchar($prompt);
	}

	/**
	 *	Prompts for user input
	 *
	 *	@param	string	$question
	 *	@return	string
	 */
	public function ask(string $question, bool $arrow = true)
	{
		return Co::prompt($question, null, $arrow);
	}

	/**
	 *	Prompts for user input without revealing it at screen
	 *
	 *	@param	string	$question
	 *	@return	string
	 */
	public function secret(string $question)
	{
		return Co::silent($question);
	}

	/**
	 *	Prompts for user confirm
	 *
	 *	@param	string	$prompt
	 *	@param	bool	$default
	 *	@return	bool
	 */
	public function confirm(string $prompt, bool $default = false)
	{
		$resp = strtolower(trim(Co::prompt($prompt)));
		
		if (in_array($resp, ['y','yes','1'])) {
			return true;
		} elseif (in_array($resp, ['n','no','0'])) {
			return false;
		}
		return $default;
	}

	/**
	 *	Prompts for user choice within the given option set
	 *
	 *	@param	string	$prompt
	 *	@param	array	$options
	 *	@param	int		$defaultIndex	Default choice
	 *	@param	int		$maxAttempts	how many attempts. Default is none.
	 *	@param	int		$maxSelections	how many selectable itens. Defualt is 1
	 *	@return	string
	 */
	public function choice(
		string $prompt, array $options, int $defaultIndex = 0,
		int $maxAttempts = 1, int $maxSelections = 1
	) {
		return Co::choice(
			$prompt, $options, $defaultIndex, $maxAttempts, $maxSelections
		);
	}

	/**
	 *	Sets an environment variable in the current console window
	 *
	 *	@param	string	$name	
	 *	@param	string	$value	
	 *	@return	void
	 */
	public function setEnv(string $name, string $value = null)
	{
		$this->app->setEnv($name, $value);
	}

	/**
	 *	Gets an environment variable in the current console window
	 *
	 *	@param	string	$name	
	 *	@return	string
	 */
	public function getEnv(string $name)
	{
		return $this->app->getEnv($name);
	}

	/**
	 *	Lists all environment variable names, if any
	 *
	 *	@return	array
	 */
	public function listEnv()
	{
		return $this->app->listEnv();
	}

	/**
	 *	Lists all environment variables, if any
	 *
	 *	@return	array
	 */
	public function listEnvWithValues()
	{
		return $this->app->listEnvWithValues();
	}

	/**
	 *	Outputs to the console with newline and default style
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function line(string $text)
	{
		$this->output->writeln($text);
	}

	/**
	 *	Outputs to the console
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function write(string $text)
	{
		if (!$this->silent()) {
			Formatter::make($text)->output();
		}
	}

	/**
	 *	Outputs to the console from the $x column and $y row
	 *
	 *	@param	int		$x
	 *	@param	int		$y
	 *	@param	string	$text	
	 *	@return void
	 */
	public function writeTo(int $x, int $y, string $text)
	{
		if (!$this->silent()) {
			Formatter::make($text)->outputTo($x, $y);
		}
	}

	/**
	 *	Outputs to the console inside a delimited Rect
	 *
	 *	@param	\Collei\Geometry\Rect		$rect
	 *	@param	string	$text	
	 *	@return void
	 */
	public function writeIn(Rect $rect, string $text)
	{
		if (!$this->silent()) {
			Formatter::make($text)->outputToRect($rect);
		}
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function info(string $text)
	{
		$this->output->writeln("<info>$text</info>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function comment(string $text)
	{
		$this->output->writeln("<comment>$text</comment>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function error(string $text)
	{
		$this->output->writeln("<error>$text</error>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function question(string $text)
	{
		$this->output->writeln("<question>$text</question>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function warn(string $text)
	{
		$this->output->writeln("<warn>$text</warn>");
	}

	/**
	 *	Outputs one or more blank lines
	 *
	 *	@param	int		$count
	 *	@return void
	 */
	public function newLine(int $count = 1)
	{
		$this->output->newLine($count);
	}


	/**
	 *	Incorporate the command line values into Command signature variables
	 *
	 *	@param	\Collei\Console\Commands\Command	$com
	 *	@param	\Collei\Console\CommandLine			$commandLine
	 *	@return	void
	 */
	public static function incorporate(Command $com, CommandLine $commandLine)
	{
		$com->setArguments($commandLine);
	}

	/**
	 *	Does the matching between the command and the command line
	 *
	 *	@param	\Collei\Console\Commands\Command	$com
	 *	@param	string	$commandLine
	 *	@return	bool	true if command line matches, false otherwise
	 */
	public static function matches(Command $com, string $commandLine)
	{
		if (empty($commandLine)) {
			return false;
		}
		//
		$commandLine = trim($commandLine);
		$commandFullName = $com->commandName
			. ($com->commandSubName ? (':' . $com->commandSubName) : '');
		$pattern = '#^(([^\\s\\:]+)(\\:([^\\s]+))?)#i';
		//
		if (\preg_match($pattern, $commandLine, $info)) {
			return $info[1] == $commandFullName;
		}
		//
		return false;
	}

	/**
	 *	Builds a new instance of Command and binds the closure to it
	 *
	 *	@param	string		$signature
	 *	@param	\Closure	$closure
	 *	@return	\Collei\Console\Commands\Command
	 */
	public static function makeFromClosure(string $signature, Closure $closure)
	{
		$self = new Command();
		$self->signature = $signature;
		$self->setClosure($closure);
		$self->compileRegexFromSignature();
		//
		return $self;
	}

}

