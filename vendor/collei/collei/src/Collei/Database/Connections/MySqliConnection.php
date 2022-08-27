<?php
namespace Collei\Database\Connections;

use mysqli;
use Exception;
use Closure;
use Collei\Database\Box\QueryBox;
use Collei\Database\Query\DatabaseQueryException;
use Collei\Database\Query\Dialects\MySqlDialect;
use Collei\Utils\Arr;
use Collei\Utils\Parsers\DsnParser;

/**
 *	Encapsulates the connection features and tasks
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class MySqliConnection extends Connection
{
	public const REGEX_PREPARED = '/(.*WHERE.*\?.*|.*VALUES\s*\(.*,?\s*\?.*)/i';

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
		'options' => []
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

		logerror('DBCE: ' . get_class($this), $whereItOccurred . ': ' . $info . ', ' . print_r($info, true));

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
	 *	@throws InvalidArgumentException, RuntimeException
	 */
	protected function openHandle($dsn, string $user = '', string $pass = '', array $options = [])
	{
		$values = [];
		$defaults = [];

		if (!empty($user))
		{
			$defaults['user'] = $user;
		}

		if (!empty($pass))
		{
			$defaults['password'] = $pass;
		}

		$defaults['charset'] = $options['charset'] ?? 'utf8mb4';

		if (DsnParser::parsePdoMysql($dsn, $values, $defaults))
		{
			$this->handle = new mysqli($values['host'], $values['user'], $values['password'], $values['dbname']);

			if ($this->handle->connect_errno)
			{
				throw new RuntimeException('mysqli connection error: ' . $this->handle->connect_error);
			}

			/* Set the desired charset after establishing a connection */
			$this->handle->set_charset($values['charset']);

			if ($this->handle->errno)
			{
				throw new RuntimeException('mysqli error: ' . $this->handle->error);
			}

			$this->is_open = true;
		}
		else
		{
			$this->is_open = false;
			$this->addError('malformed DSN', -1, $dsn);

			throw new InvalidArgumentException('DSN value is invalid or malformed.');	
		}
	}

	/**
	 *	Closes the connection
	 *
	 *	@return	void
	 */
	protected function closeHandle()
	{
		$this->handle->close()
		;
		$this->handle = null;
		$this->is_open = false;
	}

	/**
	 *	Does the binding work
	 *
	 *	@param	mixed	$stmt
	 *	@param	mixed	$data
	 *	@return	void
	 */
	protected function binder($stmt, $data, $callerInfo = null)
	{
		if (count($data) > 0)
		{
			$types = '';
			$rowdata = [];

			foreach ($data as $n => $v)
			{
				$type = (is_double($v) ? 'd' : (is_int($v) ? 'i' : 's'));
				$types .= $type;
				$rowdata[] = $v;
			}

			$stmt->bind_param($types, ...$rowdata);
		}
	}

	/**
	 *	Executes select query and returns the resulting rows
	 *
	 *	@param	string	$sql
	 *	@return	array
	 */
	protected function selectQuery(string $sql, array $params)
	{
		$result = [];
		$stmt = null;

		try
		{
			$stmt = $this->handle->prepare($sql);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $sql, __METHOD__ . ' » mysqli::prepare(): ', $row);
		}

		//logit(__METHOD__, print_r([ 'sql' => $sql, 'conn' => $this->getName() ], true));

		$this->binder($stmt, $params, __METHOD__);
		$stmt->execute();
		$rowset = $stmt->get_result();

		//logit(__METHOD__, print_r([ 'sql' => $sql, 'rowset.type' => gettype($rowset), 'conn' => $this->getName() ], true));

		while ($row = $rowset->fetch_assoc())
		{
			$result[] = $row;
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
			$this->processError($ex, $sql, __METHOD__ . ' » mysqli::prepare(): ', $row);
		}

		//logit(__METHOD__, print_r([ 'sql' => $sql, 'row' => $row, 'conn' => $this->getName() ], true));

		unset($data['created_at']);
		unset($data['updated_at']);

		$this->binder($stmt, $row, __METHOD__);
		$stmt->execute();
		$last_id = $stmt->insert_id;

		return $last_id;
	}

	/**
	 *	Executes update query and returns the number of affected rows (may depends on the underlying db engine)
	 *
	 *	@param	string	$sql
	 *	@param	array	$data
	 *	@param	bool	$usingNamedParameters
	 *	@return	int
	 */
	protected function updateQuery(string $sql, array $data, bool $usingNamedParameters = false)
	{
		$stmt = null;

		try
		{
			$stmt = $this->handle->prepare($sql);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $sql, __METHOD__ . ' » mysqli::prepare(): ', $data);
			return 0;
		}

		foreach ($data as $n => $v)
		{
			if ($v == ':updated_at')
			{
				//$data[$n] = $this->dialect->functions('current_timestamp');
				break;
			}
		}

		//logit($callerInfo.' from('.__METHOD__.') ', print_r([ 'stmt' => $stmt, 'data' => $data ], true));		

		$this->binder($stmt, $data, __METHOD__);
		$stmt->execute();
		$rows_affected = $stmt->affected_rows;

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

		//logit(__METHOD__, print_r([ 'sql' => $sql, 'row' => $data, 'conn' => $this->getName() ], true));

		try
		{
			$stmt = $this->handle->prepare($sql);
		}
		catch (Exception $ex)
		{
			$this->processError($ex, $sql, __METHOD__ . ' » mysqli::prepare(): ', $data);
			return 0;
		}

		$this->binder($stmt, $data, __METHOD__);
		$stmt->execute();
		$rows_affected = $stmt->affected_rows;

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
			$this->handle->begin_transaction();
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

		$this->dialect = new MySqlDialect();
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
	 *	Change the active database for the connection
	 *
	 *	@return	bool
	 */
	public function changeDatabase(string $database)
	{
		if (parent::changeDatabase($database))
		{
			if ($this->is_open)
			{
				$this->handle->select_db($database);
			}
		}
	}

	/**
	 *	Opens the connection with the parameters already set by the constructor
	 *
	 *	@return	void
	 */
	public function open()
	{
		try
		{
			$this->openHandle(
				$this->conn_data['dsn'],
				$this->conn_data['username'],
				$this->conn_data['password'],
				$this->conn_data['options']
			);

			$this->handle->select_db($this->conn_data['database']);
		}
		catch (Exception $ex)
		{
			$str = print_r($this->conn_data, true);
			$this->processError($ex, $ex->getMessage(), __METHOD__ . ' » mysqli::connect(): ', $str);
		}
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
	 *	@param	array	$data
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

