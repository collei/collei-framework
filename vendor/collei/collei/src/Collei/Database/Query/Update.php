<?php
namespace Collei\Database\Query;

use Closure;
use Collei\Database\Box\QueryBox;
use Collei\Database\Meta\DS;
use Collei\Database\Query\DB;
use Collei\Database\Query\Query;
use Collei\Database\Query\Clauses\Where;
use Collei\Database\DatabaseException;
use Collei\Support\Arr;

/**
 *	Encapsulates update tasks
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class Update extends QueryBox implements Query
{
	/**
	 *	@var array $fields - indexed by field names, must contain the values
	 */
	private $fields = [];

	/**
	 *	@var array $keys - indexed by field names, must contain the values
	 */
	private $keys = [];

	/**
	 *	@var \Collei\Database\Query\Clauses\Where $where
	 */
	private $where = null;

	/**
	 *	@var \Collei\Database\Connections\Connection $db_connection
	 */
	private $db_connection = null;

	/**
	 *	Initializes a new instance
	 *
	 *	@param	string	$tableName	name of the table
	 */
	private function __construct(string $tableName)
	{
		$this->setTableName($tableName);

		$instance = $this->setTableInstance(DS::getTable($tableName));

		if (!is_null($instance))
		{
			$this->instance = $instance;
			$this->db_connection = $instance->getDatabase()->getConnection();
		}
	}

	/**
	 *	Assigns a Where instance to this Update query
	 *
	 *	@param	\Collei\Database\Query\Where	$where
	 *	@return	void
	 */
	protected function setWhereClause(Where $where)
	{
		$this->where = $where;
	}

	/**
	 *	Add a value to be updated
	 *
	 *	@param	$name	string	Name of the field to be updated with $value
	 *	@param	$value	mixed	New value for the field
	 *	@return	\Collei\Database\Query\Update
	 */
	public function set(string $name, $value)
	{
		$this->fields[$name] = $value;

		return $this;
	}

	/**
	 *	Returns a simple Where clause
	 *
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function where()
	{
		$clause = Where::new();
		$clause->setQueryInstance($this);

		return $this->where = $clause;
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
	 *	Performs physical database update of the records through query
	 *
	 *	@return	int|array	the inserted id of the one only row, or a list of ids of the inserted rows 	
	 */
	public function done()
	{
		$table_name = $this->getTableName();
		$fields = $this->fields;
		$field_count = count($fields);
		$named_params = false;

		if (!is_null($this->db_connection))
		{
			$sql = '';

			if ($field_count > 0)
			{
				$set_fields = [];

				if ($named_params)
				{
					foreach ($fields as $n => $v)
					{
						$set_fields[$n] = ":$n";
					}
				}
				else
				{
					$set_fields = Arr::repeats($fields, '?');
				}

				$sql = $this->db_connection->dialect->update($table_name, $set_fields, $this->where ?? '');

				$this->db_connection->update($sql, $fields, false);
			}
		}

		return true;
	}

	/**
	 *	Returns a new instance of the class, linked with the table
	 *
	 *	@param	string	name of the table	
	 *	@return	\Collei\Database\Query\Update		
	 */
	public static function make(string $tableName)
	{
		return new static($tableName);
	}

}


