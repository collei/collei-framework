<?php
namespace Collei\Database\Query;

use Closure;
use Collei\Database\Box\QueryBox;
use Collei\Database\Meta\DS;
use Collei\Database\Query\DB;
use Collei\Database\Query\Query;
use Collei\Database\Query\Clauses\Where;
use Collei\Database\Query\Clauses\OrderBy;
use Collei\Database\Query\Clauses\Join;
use Collei\Database\DatabaseException;
use Collei\Utils\Arr;

/**
 *	Embodies the delete query properties
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class Insert extends QueryBox implements Query
{
	/**
	 *	@var array $fields
	 */
	private $fields = [];

	/**
	 *	@var array $keys
	 */
	private $keys = [];

	/**
	 *	@var array $records
	 */
	private $records = [];

	/**
	 *	@var \Collei\Database\Connections\Connection $db_connection
	 */
	private $db_connection = null;

	/**
	 *	Builds and instantiates
	 *
	 *	@param	string	$tableName
	 */
	private function __construct(string $tableName)
	{
		$this->setTableName($tableName);

		$instance = $this->setTableInstance(DS::getTable($tableName));
		if (!is_null($instance))
		{
			$this->db_connection = $instance->getDatabase()->getConnection();
		}
	}

	/**
	 *	Adds one or more rows to be inserted
	 *
	 *	@param	array|array[array]	Array of field values indexed by its names, or an array of these arrays
	 *	@return	\Collei\Database\Query\Insert
	 */
	public function insert(array $fields)
	{
		if (Arr::hasLines($fields))
		{
			foreach ($fields as $line)
			{
				$this->records[] = $line;
			}
		}
		else
		{
			$this->records[] = $fields;
		}

		return $this;
	}

	/**
	 *	Execute a query (implements \Collei\Database\Query\Query)
	 *
	 *	@return	mixed
	 */
	public function execute()
	{
		return $this->done();
	}

	/**
	 *	Performs physical database insertion of the records through query
	 *
	 *	@return	int|array	the inserted id of the one only row, or a list of ids of the inserted rows 	
	 */
	public function done()
	{
		$table_name = $this->getTableName();
		$row_count = count($this->records);
		$named_params = false;
		$result_id = 0;

		if (!is_null($this->db_connection))
		{
			$sql = '';

			if ($row_count >= 1)
			{
				$record = $this->records[0];
				$placeholders = [];

				if ($named_params)
				{
					foreach ($record as $n => $v)
					{
						$placeholders[] = ":$n";
					}
				}
				else
				{
					$placeholders = Arr::repeats($record, '?');
				}

				$sql = $this->db_connection->dialect->insertOnly($table_name, Arr::keys($record), $placeholders);
			}

			if ($row_count == 1)
			{
				$result_id = $this->db_connection->insertOne($sql, $this->records[0], $named_params);
			}
			elseif ($row_count > 1)
			{
				$result_id = $this->db_connection->insertMany($sql, $this->records, $named_params);
			}

			$this->records = [];
		}

		return $result_id;
	}

	/**
	 *	Returns a new instance of the class, linked with the table
	 *
	 *	@param	string	name of the table	
	 *	@return	\Collei\Database\Query\Insert		
	 */
	public static function into(string $tableName)
	{
		return new static($tableName);
	}

}


