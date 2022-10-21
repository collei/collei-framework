<?php
namespace Collei\Console;

use Collei\Console\Commands\Command;
use Collei\Console\Commands\CommandEntries;
use Collei\Support\Str;
use Closure;

/**
 *	Embodies helper methods for creating inline commands
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-xx
 */
class Cyno
{
	/**
	 *	Creates a command
	 *
	 *	@param	string		$signature	
	 *	@param	\Closure	$command	
	 *	@return	void
	 */
	public static function command(string $signature, Closure $command)
	{
		CommandEntries::addClosure($signature, $command);
	}

	/**
	 *	Adds a command from a command class
	 *
	 *	@param	mixed		$classOrInstance
	 *	@return	void
	 */
	public static function entry($className)
	{
		if (is_a($className, Command::class, true)) {
			return new $className();
		}
		//
		return null;
	}

	/**
	 *	Returns all available commands.
	 *	If $prefix is given, returns those which starts with
	 *	such string.
	 *
	 *	@param	string	$prefix
	 *	@return	array
	 */
	public static function available(string $prefix = null)
	{
		$availables = CommandEntries::listAvailable();
		//
		if (empty($prefix)) {
			return $availables;
		}
		//
		$list = [];
		foreach ($availables as $k => $item) {
			if (Str::startsWith($item, $prefix)) {
				$list[] = $item;
			}
		}
		//
		return $list;
	}

}

