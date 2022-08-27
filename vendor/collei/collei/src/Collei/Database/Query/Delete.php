<?php
namespace Collei\Database\Query;

use Closure;
use Collei\Database\Box\QueryBox;
use Collei\Database\Meta\DS;
use Collei\Database\Query\DB;
use Collei\Database\Query\Query;
use Collei\Database\Query\Clauses\Where;
use Collei\Database\DatabaseException;
use Collei\Utils\Arr;

/**
 *	Embodies the delete query properties
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class Delete extends QueryBox implements Query
{
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
			$this->db_connection = $instance->getDatabase()->getConnection();
		}
	}

	/**
	 *	Assigns a Where instance to this Select query
	 *
	 *	@param	\Collei\Database\Query\Where	$where
	 *	@return	void
	 */
	protected function setWhereClause(Where $where)
	{
		$this->where = $where;
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

		if (!is_null($this->db_connection))
		{
			$sql = $this->db_connection->dialect->delete($table_name, $this->where ?? '');

			return $this->db_connection->delete($sql, [], false) > 0;
		}

		return false;
	}

	/**
	 *	Returns a new instance of the class, linked with the table
	 *
	 *	@param	string	name of the table	
	 *	@return	\Collei\Database\Query\Delete		
	 */
	public static function make(string $tableName)
	{
		return new static($tableName);
	}

}


