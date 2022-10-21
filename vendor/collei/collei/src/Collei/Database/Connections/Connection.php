<?php
namespace Collei\Database\Connections;

use PDO;
use PDOException;
use PDOStatement;
use Exception;
use Closure;
use Collei\Database\Box\QueryBox;
use Collei\Database\Query\DatabaseQueryException;
use Collei\Database\Query\Dialects\Dialect;
use Collei\Support\Arr;

/**
 *	Encapsulates the connection features and tasks
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class Connection
{
	/**
	 *	@property	string	$name
	 *	@property	instanceof \Collei\Database\Query\Dialects\Dialect $dialect
	 *	@property	string	$dsn
	 *	@property	string	$database
	 *	@property	string	$username
	 *	@property	array	$options
	 *	@property	$name
	 */
	public function __get($name)
	{
		if (in_array($name, ['name', 'dialect']))
		{
			return $this->$name;
		}
		if (in_array($name, ['dsn','database','username','options']))
		{
			return $this->conn_data[$name];
		}
	}


	/**
	 *	@var string $name
	 */
	protected $name = null;

	/**
	 *	@var \PDOConnection $handle
	 */
	protected $handle;

	/**
	 *	@var \Collei\Database\Query\Dialects\Dialect $dialect
	 */
	protected $dialect;

	/**
	 *	@var bool $is_open
	 */
	private $is_open = true;

	/**
	 *	@var array $errors
	 */
	private $errors = [];

	/**
	 *	@var array $conn_data
	 */
	private $conn_data = [
		'dsn' => '',
		'database' => '',
		'username' => '',
		'password' => '',
		'options' => [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8';",
		]
	];

	/**
	 *	Register errors
	 *
	 *	@param	mixed	$type	
	 *	@param	mixed	$code		
	 *	@param	string	$message	
	 *	@return	void	
	 */
	protected function addError($type, $code, string $message)
	{
		$this->errors[] = [
			'type' => $type,
			'code' => $code,
			'message' => $message,
		];
	}

	/**
	 *	Process error and exception messages
	 *
	 *	@param	\Exception	$ex
	 *	@param	string		$query
	 *	@param	string		$whereItOccurred
	 *	@param	array		$data
	 *	@return	void
	 */
	protected function processError(Exception $ex, string $query, string $whereItOccurred, array $data = null)
	{
		$pdo_error = $this->handle->errorInfo();
		$info = print_r([
			'pdo_error' => $pdo_error,
			'sql' => $query,
			'data' => ($data ?? ''),
			'exception' => get_class($ex),
			'code' => $ex->getCode(),
			'message' => $ex->getMessage()
		], true);
		//
		logerror('DBCE: ' . get_class($this), $whereItOccurred . ': ' . $info . ', ' . print_r($info, true));
		//
		$this->addError(get_class($ex), $ex->getCode(), $ex->getMessage());		
		$this->addError('PDO', -1, 'ST: ' . print_r($info, true));
	}

	/**
	 *	Opens the connection
	 *
	 *	@param	mixed	$dsn
	 *	@param	string	$user
	 *	@param	string	$pass
	 *	@param	array	$options
	 *	@return	void
	 */
	protected function openHandle($dsn, string $user = '', string $pass = '', array $options = [])
	{
		try {
			$this->handle = new PDO($dsn, $user, $pass, $options);
			if (!is_null($this->handle)) {
				$this->is_open = true;
			}
		} catch (Exception $ex) {
			$this->is_open = false;
			$this->addError(get_class($ex), $ex->getCode(), $ex->getMessage());
		}
	}

	/**
	 *	Closes the connection
	 *
	 *	@return	void
	 */
	protected function closeHandle()
	{
		$this->handle = null;
		$this->is_open = false;
	}

	/**
	 *	Executes select query and returns the resulting rows
	 *
	 *	@param	string	$sql
	 *	@return	array
	 */
	protected function selectQuery(string $sql, array $data)
	{
		$stmt = null;
		$result = [];

		try
		{
			$stmt = $this->handle->prepare($sql);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $sql, __METHOD__ . ' » PDO::prepare(): ', $row);

			return null;
		}

		$i = 0;
		foreach ($data as $n => $v)
		{
			$stmt->bindValue(++$i, $v);
		}

		$stmt->execute();
		$rowset = $stmt->fetchAll();
		$stmt->closeCursor();

		if (is_array($rowset) || $rowset instanceof PDOStatement)
		{
			foreach ($rowset as $row)
			{
				$result[] = $row;
			}
		}

		return $result;
	}

	/**
	 *	Executes insert query and returns last inserted id (may depends on the underlying db engine)
	 *
	 *	@param	string	$sql
	 *	@param	array	$row
	 *	@param	bool	$usingNamedParameters
	 *	@return	int
	 */
	protected function insertQuery(string $sql, array $row, bool $usingNamedParameters = false)
	{
		$stmt = null;

		try
		{
			$stmt = $this->handle->prepare($sql);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $sql, __METHOD__ . ' » PDO::prepare(): ', $row);
		}

		if ($usingNamedParameters)
		{
			foreach ($row as $n => $v)
			{
				$stmt->bindValue($n, $v);
			}
		}
		else
		{
			$i = 0;
			foreach ($row as $n => $v)
			{
				$stmt->bindValue(++$i, $v);
			}
		}

		$stmt->execute();
		$last_id = $this->handle->lastInsertId();

		return $last_id;
	}

	/**
	 *	Executes update query and returns the number of affected rows (may depends on the underlying db engine)
	 *
	 *	@param	string	$sql
	 *	@param	array	$row
	 *	@param	bool	$usingNamedParameters
	 *	@return	int
	 */
	protected function updateQuery(string $sql, array $data, bool $usingNamedParameters = false)
	{
		try
		{
			$stmt = $this->handle->prepare($sql);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $sql, __METHOD__ . ' » PDO::prepare(): ', $row);
			return 0;
		}

		if ($usingNamedParameters)
		{
			foreach ($data as $n => $v)
			{
				$stmt->bindValue($n, $v);
			}
		}
		else
		{
			$i = 0;
			foreach ($data as $n => $v)
			{
				$stmt->bindValue(++$i, $v);
			}
		}

		$stmt->execute();
		$rows_affected = $stmt->rowCount();

		return $rows_affected;
	}

	/**
	 *	Executes deletion and returns the number of affected rows (may depends on the underlying db engine)
	 *
	 *	@param	string	$sql
	 *	@param	bool	$usingNamedParameters
	 *	@return	int
	 */
	protected function deleteQuery(string $sql, array $data, bool $usingNamedParameters = false)
	{
		$stmt = null;

		try
		{
			$stmt = $this->handle->prepare($sql);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $sql, __METHOD__ . ' » PDO::prepare(): ', $row);
			return 0;
		}

		if ($usingNamedParameters)
		{
			foreach ($data as $n => $v)
			{
				$stmt->bindValue($n, $v);
			}
		}
		else
		{
			$i = 0;
			foreach ($data as $n => $v)
			{
				$stmt->bindValue(++$i, $v);
			}
		}

		$stmt->execute();
		$rows_affected = $stmt->rowCount();

		return $rows_affected;
	}

	/**
	 *	Run several queries in a single transaction
	 *
	 *	@param	\Closure	$bunch
	 *	@return	mixed
	 */
	protected function transactBunch(Closure $bunch)
	{
		$result = 0;

		try
		{
			$this->handle->beginTransaction();
			$result = $bunch();
			$this->handle->commit();			
		}
		catch (Exception $ex)
		{
			$this->handle->rollback();

			throw new DatabaseQueryException('There are errors during transaction inside transact($bunch).');
		}

		return $result;
	}

	/**
	 *	Initializes a new instance
	 *
	 *	@param	mixed	$dsn
	 *	@param	string	$database
	 *	@param	string	$username
	 *	@param	string	$password
	 */
	public function __construct($dsn, string $database = '', string $username = '', string $password = '')
	{
		$this->conn_data['dsn'] = $dsn;
		$this->conn_data['database'] = $database;
		$this->conn_data['username'] = $username;
		$this->conn_data['password'] = $password;

		$this->dialect = new CommonDialect();
	}

	/**
	 *	Finalizes this instance
	 *
	 *	@return	void
	 */
	public function __destruct()
	{
		if (is_array($this->errors))
		{
			foreach ($this->errors as $error)
			{
				logerror('DBCE: ' . get_class($this), print_r($error, true));
			}
		}

		$this->is_open = false;
		$this->conn_data = null;
		$this->handle = null;
		$this->errors = null;
	}

	/**
	 *	Sets the name of the connection (if it is currently unnamed)
	 *
	 *	@param	string	$name
	 *	@return	bool
	 */
	public final function setName(string $name = null)
	{
		if (is_null($this->name))
		{
			if (empty($name))
			{
				$name = 'cdbc:' . Str::random(27);
			}

			$this->name = $name;

			return true;
		}
		//
		return false;
	}

	/**
	 *	Retrieves the name of the connection
	 *
	 *	@return	string
	 */
	public final function getName()
	{
		return $this->name ?? '';
	}

	/**
	 *	Change the active database for the connection
	 *
	 *	@return	bool
	 */
	public function changeDatabase(string $database)
	{
		if ($database != $this->conn_data['database'])
		{
			$this->conn_data['database'] = $database;

			return true;
		}
		//
		return false;
	}

	/**
	 *	Opens the connection with the parameters already set by the constructor
	 *
	 *	@return	void
	 */
	public function open()
	{
		$this->openHandle(
			$this->conn_data['dsn'],
			$this->conn_data['username'],
			$this->conn_data['password'],
			$this->conn_data['options']
		);
	}

	/**
	 *	Closes the connection
	 *
	 *	@return	void
	 */
	public function close()
	{
		$this->closeHandle();
	}

	/**
	 *	Performs select query
	 *
	 *	@param	string	$query	
	 *	@return	mixed
	 */
	public function select(string $query, array $data = [])
	{
		$results = 0;

		try
		{
			$results = $this->selectQuery($query, $data);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $query, __METHOD__);
			return null;
		}

		return $results;
	}

	/**
	 *	Performs insertion of one row
	 *
	 *	@param	string	$query
	 *	@param	array	$row
	 *	@param	bool	$useNamedParams
	 *	@return	mixed
	 */
	public function insertOne(string $query, array $row, bool $useNamedParams = false)
	{
		$results = 0;

		try
		{
			$results = $this->insertQuery($query, $row, $useNamedParams);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $query, __METHOD__);
			return null;
		}

		return $results;
	}

	/**
	 *	Performs insertion of several rows
	 *
	 *	@param	string	$query
	 *	@param	array	$rows
	 *	@param	bool	$useNamedParams
	 *	@return	mixed
	 */
	public function insertMany(string $query, array $rows, bool $useNamedParams = false)
	{
		$results = 0;

		try
		{
			$results = $this->transactBunch(function() use ($query, $rows, $useNamedParams){
				$list_ids = [];
				foreach ($rows as $row)
				{
					$list_ids[] = $this->insertQuery($query, $row, $useNamedParams);
				}
				return $list_ids;
			});
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $query, __METHOD__);
			return null;
		}

		return $results;
	}

	/**
	 *	Performs update
	 *
	 *	@param	string	$query
	 *	@param	array	$data
	 *	@param	bool	$useNamedParams
	 *	@return	mixed
	 */
	public function update(string $query, array $data, bool $useNamedParams = false)
	{
		$results = 0;

		try
		{
			$results = $this->updateQuery($query, $data, $useNamedParams);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $query, __METHOD__ . ' PDO::prepare() ');
			return null;
		}

		return $results;
	}

	/**
	 *	Performs deletion
	 *
	 *	@param	string	$query
	 *	@param	bool	$useNamedParams
	 *	@return	mixed
	 */
	public function delete(string $query, array $data, bool $useNamedParams = false)
	{
		$results = 0;

		try
		{
			$results = $this->deleteQuery($query, $data, $useNamedParams);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $query, __METHOD__ . ' PDO::prepare() ');
			return null;
		}

		return $results;
	}

	/**
	 *	Returns if there is an error of such $code
	 *
	 *	@return	bool
	 */
	public function hasError($code)
	{
		$code = '' . $code . '';
		return array_key_exists($code, $this->errors);
	}

	/**
	 *	Returns if there are registered errors
	 *
	 *	@return	bool
	 */
	public function hasErrors()
	{
		return count($this->errors) > 0;
	}

	/**
	 *	Returns the last registered error
	 *
	 *	@return	array|null
	 */
	public function lastError()
	{
		if ($this->hasErrors())
		{
			return $this->errors[count($this->errors) - 1];
		}
		return null;
	}

	/**
	 *	Returns the index of last registered error
	 *
	 *	@return	int
	 */
	public function lastErrorIndex()
	{
		if ($this->hasErrors())
		{
			return count($this->errors) - 1;
		}
		return 0;
	}

	/**
	 *	Performs a transaction
	 *
	 *	@param	\Closure	$bunch
	 *	@return	mixed
	 */
	public function transact(Closure $bunch)
	{
		$result = 0;

		try
		{
			$this->handle->beginTransaction();
			$lei_before = $this->lastErrorIndex();
			$result = $bunch();
			$lei_after = $this->lastErrorIndex();

			if ($lei_after > $lei_before)
			{
				throw new DatabaseQueryException('There are errors during transaction inside transact($bunch).');
			}

			$this->handle->commit();			
		}
		catch (Exception $ex)
		{
			$this->errors[] = [
				'type' => get_class($ex),
				'code' => '' . $ex->getCode() . '',
				'message' => 'Error on transact($bunch): ' . $ex->getMessage(),
			];
			$this->handle->rollback();
			return false;
		}

		return $result;
	}

	/**
	 *	Returns all registered errors
	 *
	 *	@return	array
	 */
	public function getErrors()
	{
		$errors = $this->errors;
		return $errors;
	}

}



/**
 *	Just a materialized form of abstract Dialect class
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-12-07
 */
class CommonDialect extends Dialect
{
}

