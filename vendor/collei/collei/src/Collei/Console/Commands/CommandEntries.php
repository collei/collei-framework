<?php
namespace Collei\Console\Commands;

use Collei\Console\CommandLine;
use Collei\Console\Co;
use Collei\Console\Commands\Command;
use Collei\Support\Arr;
use Closure;

/** 
 *	Encapsulates the retainer of the set of commands 
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-xx
 */
class CommandEntries
{
	/**
	 *	@var array
	 */
	private static $entries = [];

	/**
	 *	Parses the command signature into an array info about the parameters
	 *	the command expects
	 *
	 *	@param	string	$signature
	 *	@param	array	&$info
	 *	@return	bool
	 */
	public static function parseSignature(string $signature, array &$info)
	{
		$pattern = '#\\{\\s*((\\w[\\w:\\-]*)(\\?\\*|[?*])?(=("[^"]*"|\''
			. '[^\']*\'|[^\\s}]*)?)?|(-(\\w)\\|)?(--(\\w[\\w\\-]*\\w))(=("'
			. '[^"]*"|\'[^\']*\'|[^\\s}]*)?)?)(\\s*:\\s*([^}]+))?\\s*\\}#i';
		//
		$line = str_replace(["\r","\n"], '', trim($signature));
		$list = ($args = []);
		//
		if (preg_match_all($pattern, $line, $list, PREG_SET_ORDER))
		{
			$first = true;
			//
			foreach ($list as $item)
			{
				$attributes = Arr::create(
					'name','type','description','shortcut','defaultValue',
					'isOptional','isArray'
				);
				// it's an option
				if (!empty($item[9] ?? ''))
				{
					$attributes['name'] = trim($item[9]);
					$attributes['type'] = 'OPT';
					$attributes['description'] = trim($item[13] ?? '');
					$attributes['shortcut'] = trim($item[7] ?? '');
					//
					$default = trim($item[11] ?? '');
					//
					$attributes['defaultValue'] = empty($default)
						? false 
						: (($default == '*') ? [] : $default);
					$attributes['isOptional'] = true;
					$attributes['isArray'] = ($default == '*');
				}
				// it's an argument
				elseif (!empty($item[2] ?? ''))
				{
					$attributes['type'] = 'ARG';
					$attributes['name'] = trim($item[2]);
					$attributes['description'] = trim($item[13] ?? '');
					$attributes['defaultValue'] = trim($item[5] ?? '');
					//
					$signal = trim($item[3] ?? '');
					$optional = strpos($signal, '?') !== false;
					$multiple = strpos($signal, '*') !== false;
					//
					$attributes['isOptional'] = $optional;
					$attributes['isArray'] = $multiple;
				}
				//
				$args[] = $attributes;
				$first = false;
			}
			//
			$info = $args;
			//
			return true;
		}
		//
		return preg_match('#^([^\\s\\:]+)(\\:([^\\s]+))?\\s*#i', trim($signature));
	}

	/**
	 *	Adds the command and its signature to the list
	 *
	 *	@param	string	$entrySpec
	 *	@param	\Collei\Console\Commands\Command	$instance
	 *	@return	\Collei\Console\Commands\Command
	 */
	public static function add(string $entrySpec, Command $instance)
	{
		if (!empty($entrySpec))
		{
			return self::$entries[$entrySpec] = $instance;
		}
	}

	/**
	 *	Adds the command closure and its signature to the list
	 *
	 *	@param	string		$entrySpec
	 *	@param	\Closure	$closure
	 *	@return	\Collei\Console\Commands\Command
	 */
	public static function addClosure(string $entrySpec, Closure $closure)
	{
		return self::add(
			$entrySpec,
			Command::makeFromClosure($entrySpec, $closure),
		);
	}

	/**
	 *	Asks for the command corresponding to the $commandLine and returns it - if any
	 *
	 *	@param	\Collei\Console\CommandLine	$commandLine
	 *	@return	\Collei\Console\Commands\Command|false
	 */
	public static function find(CommandLine $commandLine)
	{
		foreach (self::$entries as $entry) {
			if (Command::matches($entry, (string)$commandLine)) {
				return $entry;
			}
		}
		//
		return false;
	}

	/**
	 *	Asks for the command corresponding to the $commandName and returns it - if any
	 *
	 *	@param	string	$commandName
	 *	@return	\Collei\Console\Commands\Command|false
	 */
	public static function findByName(string $commandName)
	{
		foreach (self::$entries as $entry) {
			if (Command::matches($entry, $commandName)) {
				return $entry;
			}
		}
		//
		return false;
	}

	/**
	 *	Return a list of the currently loaded and available commands
	 *	for user use.
	 *
	 *	@return	array
	 */
	public static function listAvailable()
	{
		return \array_map(
			function($entry) {
				return $entry->getName();
			},
			self::$entries
		);
	}

}


