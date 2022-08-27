<?php
namespace Collei\Database\Connections;

use Collei\Database\Connections\Connection;
use Collei\Database\Connections\MySqliConnection;
use Collei\Database\Connections\MsSqlServerConnection;
/**
 *	Embodies connection tasks
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-07-xx
 */
class Connector
{
	/**
	 *	@var string
	 */
	private $driver = '';

	/**
	 *	@var string
	 */
	private $dsn = '';

	/**
	 *	@var string
	 */
	private $host = '';

	/**
	 *	@var string
	 */
	private $user = '';

	/**
	 *	@var string
	 */
	private $pass = '';

	/**
	 *	@var string
	 */
	private $charset = '';

	/**
	 *	Returns the database user
	 *
	 *	@return	string
	 */
	protected function getUser()
	{
		return $this->user;
	}

	/**
	 *	Returns the database password
	 *
	 *	@return	string
	 */
	protected function getPass()
	{
		return $this->pass;
	}

	/**
	 *	Returns the database host
	 *
	 *	@return	string
	 */
	protected function getHost()
	{
		return $this->host;
	}

	/**
	 *	Returns the database connection string
	 *
	 *	@return	string
	 */
	protected function getDSN()
	{
		return $this->dsn;
	}

	/**
	 *	Connects and returns the created connection
	 *
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function connect()
	{
		return Connector::make(
			$this->driver,
			$this->dsn,
			$this->user,
			$this->pass, ''
		);
	}

	/**
	 *	Sets the connection driver
	 *
	 *	@return	string	$driver
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function driver(string $driver)
	{
		$this->driver = $driver;
		return $this;
	}

	/**
	 *	Sets the connection string
	 *
	 *	@return	string	$dsn
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function dsn(string $dsn)
	{
		$this->dsn = $dsn;
		return $this;
	}
	
	/**
	 *	Sets the database host
	 *
	 *	@return	string	$host
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function host(string $host)
	{
		$this->host = $host;
		return $this;
	}
	
	/**
	 *	Sets the database user
	 *
	 *	@return	string	$user
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function user(string $user)
	{
		$this->user = $user;
		return $this;
	}
	
	/**
	 *	Sets the database user password
	 *
	 *	@return	string	$pass
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function pass(string $pass)
	{
		$this->pass = $pass;
		return $this;
	}
	
	/**
	 *	Sets the connection charset
	 *
	 *	@return	string	$charset
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function charset(string $charset)
	{
		$this->charset = $charset;
		return $this;
	}
	
	/**
	 *	Sets database connection options
	 *
	 *	@return	string	$option
	 *	@return	mixed	$value
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function option(string $option, $value)
	{
		$this->options[$option] = $value;
		return $this;
	}
	
	/**
	 *	Sets several connection options at once
	 *
	 *	@return	array	$options	an associative array of values indexed by their names
	 *	@return	\Collei\Database\Connections\Connection
	 */
	public function options(array $options)
	{
		foreach ($options as $n => $v)
		{
			$this->options[$n] = $v;
		}
		return $this;
	}

	/**
	 *	Creates a Connection for the specified driver
	 *
	 *	@static
	 *	@param	string	$driver
	 *	@param	string	$dsn
	 *	@param	string	$username
	 *	@param	string	$password
	 *	@param	string	$db
	 *	@return	instanceof \Collei\Database\Connections\Connection
	 */

	public static function make(string $driver, string $dsn, string $username, string $password, string $db = '')
	{
		if ($driver == 'mysql')
		{
			return new MySqliConnection($dsn, $db, $username, $password);
		}
		elseif ($driver == 'sqlsrv' || $driver == 'sqlsvr' || $driver == 'sqlserver')
		{
			return new MsSqlServerConnection($dsn, $db, $username, $password);
		}
		else
		{
			return new Connection($dsn, $db, $username, $password);
		}
	}
	
}


