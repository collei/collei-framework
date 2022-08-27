<?php
namespace Collei\Database\Meta;

use Closure;
use Collei\Database\Box\DataBox;
use Collei\Database\Meta\Database;
use Collei\Database\Meta\Table;
use Collei\Utils\Files\ConfigFile;

/**
 *	Shorthand helpers in class form 
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class DS extends DataBox 
{
	/**
	 *	@var \Collei\Utils\Files\ConfigFile
	 */
	private static $conf = null;

	/**
	 *	Loads and initializes configurations from file
	 *
	 *	@return	void
	 */
	private static function init()
	{
		if (is_null(self::$conf))
		{
			$file = PLAT_CONF_GROUND . DIRECTORY_SEPARATOR . '.dbc';
			self::$conf = new ConfigFile($file);
		}
	}

	/**
	 *	Retrieves the specified configuration value
	 *
	 *	@param	string	$name
	 *	@return	mixed
	 */
	public static function conf(string $name)
	{
		self::init();

		return self::$conf->get($name);
	}

	/**
	 *	Builds and initializes a database structure from a user-defined closure
	 *
	 *	@param	string		$name
	 *	@param	\Closure	$definition
	 *	@return	void
	 */
	public static function database(string $name, Closure $definition)
	{
		self::init();

		$db = new Database($name);
		$definition($db);
		self::$databases[$name] = $db;
		$db->ensure();
	}

	/**
	 *	Returns if a specified table metadata exists in the database
	 *
	 *	@param	string	$tableName
	 *	@param	string	$database
	 *	@return	bool
	 */
	public static function hasTable(string $tableName, string $database = null)
	{
		if (!is_null($database))
		{
			if (array_key_exists($database, self::$databases))
			{
				return self::$databases[$database]->has($tableName);
			}
		}
		else
		{
			foreach (self::$databases as $database)
			{
				if ($database->has($tableName))
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	 *	Returns the specified table metadata from the database, if any
	 *
	 *	@param	string	$tableName
	 *	@param	string	$database
	 *	@return	\Collei\Database\Meta\Table
	 */
	public static function getTable(string $tableName, string $database = null)
	{
		if (!is_null($database))
		{
			if (array_key_exists($database, self::$databases))
			{
				return self::$databases[$database]->get($tableName);
			}
		}
		else
		{
			foreach (self::$databases as $database)
			{
				if ($database->has($tableName))
				{
					return $database->get($tableName);
				}
			}
		}
		return null;
	}

}


