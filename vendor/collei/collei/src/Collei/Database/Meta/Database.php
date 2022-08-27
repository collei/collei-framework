<?php
namespace Collei\Database\Meta;

use Closure;
use Collei\Database\Meta\DS;
use Collei\Database\Meta\Table;
use Collei\Utils\Collections\Properties;
use Collei\Database\Connections\Connection;
use Collei\Database\Connections\Connector;

/**
 *	Embodies database properties and metadata
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class Database
{
	/**
	 *	@var string
	 */
	private $name = '';

	/**
	 *	@var \Collei\Database\Connections\Connection
	 */
	private $connection = null;

	/**
	 *	@var array
	 */
	private $tables = [];

	/**
	 *	@var \Collei\Utils\Collections\Properties
	 */
	private $parameters = null;

	/**
	 *	Creates a new instance of database metadata
	 *
	 *	@param	string	$name
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
		$this->parameters = new Properties();
	}

	/**
	 *	Finalizes this instance of database metadata
	 *
	 *	@return	void
	 */
	public function __destruct()
	{
		$this->connection = null;
	}

	/**
	 *	Returns the database name
	 *
	 *	@return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *	Returns the database connection
	 *
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 *	Adds a new table definition based upon the closure
	 *
	 *	@param	string		$name
	 *	@param	\Closure	$structure
	 *	@return	void
	 */
	public function table(string $name, Closure $structure)
	{
		$table = new Table($name, $this);
		$structure($table);
		$this->tables[$name] = $table;

		return $table;
	}

	/**
	 *	Returns if such $tableName exists
	 *
	 *	@return	bool
	 */
	public function has(string $tableName)
	{
		return array_key_exists($tableName, $this->tables);
	}

	/**
	 *	Returns the specified table, if any
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Meta\Table
	 */
	public function get(string $tableName)
	{
		if (array_key_exists($tableName, $this->tables))
		{
			return $this->tables[$tableName];
		}
		return null;
	}

	/**
	 *	Enforces features of all fields at each table
	 *
	 *	@return	void
	 */
	public function ensure()
	{
		foreach ($this->tables as $table)
		{
			$table->ensureFields();
		}
	}

	/**
	 *	Adds the parameter
	 *
	 *	@param	string	$paramName
	 *	@param	mixed	$paramValue
	 *	@return	void
	 */
	public function parameter(string $paramName, $paramValue)
	{
		$this->parameters->add($paramName, $paramValue);
	}

	/**
	 *	Retrieves the parameter value
	 *
	 *	@param	string	$paramName
	 *	@return	mixed
	 */
	public function getParameter(string $paramName)
	{
		$this->parameters->get($paramName, '');
	}

	/**
	 *	Performs database connection
	 *
	 *	@return	void
	 */
	public function connect()
	{
		$val_drv = $this->parameters->get('driver', DS::conf('default'));
		$val_dsn = $this->parameters->get('dsn', DS::conf($val_drv . '.dsn'));
		$val_user = $this->parameters->get('username', DS::conf($val_drv . '.user'));
		$val_pass = $this->parameters->get('password', DS::conf($val_drv . '.pass'));
		$val_db = $this->parameters->get('database', DS::conf($val_drv . '.db'));

		if (empty($val_db))
		{
			$val_db = $this->name;
		}

		$this->connection = Connector::make(
			$val_drv,
			$val_dsn,
			$val_user,
			$val_pass,
			$val_db 
		);

		$db_name = $this->getName();

		$this->connection->setName("cdbc:$val_drv:$db_name");
		
		$this->connection->open();
	}

	/**
	 *	Performs table migrations
	 *
	 *	@return	void
	 */
	public function migrateAll()
	{
		if (!empty($this->connection))
		{
			foreach ($this->tables as $table)
			{
				$table->migrate();
			}
		}
	}

}


