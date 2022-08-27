<?php
namespace Collei\Database\Query;

use Closure;
use InvalidArgumentException;
use Collei\Database\Box\QueryBox;
use Collei\Database\Meta\DS;
use Collei\Database\Query\DB;
use Collei\Database\Query\Query;
use Collei\Database\Query\Clauses\Where;
use Collei\Database\Query\Clauses\OrderBy;
use Collei\Database\Query\Clauses\Join;
use Collei\Database\Yanfei\Model;
use Collei\Database\Yanfei\ModelResult;
use Collei\Database\DatabaseException;

/**
 *	Embodies the select query properties
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class Select extends QueryBox implements Query
{
	/**
	 *	@var array $fields
	 */
	private $fields = [];

	/**
	 *	@var array $joins
	 */
	private $joins = [];

	/**
	 *	@var \Collei\Database\Query\Clauses\Where $where
	 */
	private $where = null;

	/**
	 *	@var \Collei\Database\Query\Clauses\OrderBy $order
	 */
	private $order = null;

	/**
	 *	@var \Collei\Database\Connections\Connection $db_connection
	 */
	private $db_connection = null;

	/**
	 *	@var int $page
	 */
	private $page = 1;

	/**
	 *	@var int $rowsPerPage
	 */
	private $rowsPerPage = -1;

	/**
	 *	Performs initialization
	 *
	 *	@param	string	$tableName
	 *	@return	void
	 */
	private function initialize(string $tableName)
	{
		$this->setTableName($tableName);
		if (!DB::isNaive())
		{
			if (DS::hasTable($tableName))
			{
				$instance = $this->setTableInstance(DS::getTable($tableName));

				if (!is_null($instance))
				{
					$this->db_connection = $instance->getDatabase()->getConnection();
				}
			}
			else
			{
				throw new DatabaseException(
					'Table is unavailable for querying: ' .
						$tableName .
						'. Please declare it or enable naive mode.'
				);
			}
		}
		else
		{
			$instance = $this->setTableInstance(DS::getTable($tableName));

			if (!is_null($instance))
			{
				$this->db_connection = $instance->getDatabase()->getConnection();
			}
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
	 *	Returns the fields
	 *
	 *	@return	array
	 */
	protected function getFields()
	{
		$fields = $this->fields;
		//
		if (count($fields) === 0)
		{
			return ['*'];
		}
		//
		return $fields;
	}

	/**
	 *	Defines the fields for the select clause
	 *
	 *	@param	array	$fields
	 *	@return	void
	 */
	protected function setFields(array $fields)
	{
		$this->fields = $fields;
	}

	/**
	 *	Returns if the field exists
	 *
	 *	@param	string	$fieldName
	 *	@return	bool
	 */
	protected function hasField(string $fieldName)
	{
		return array_key_exists($fieldName, $this->fields);
	}

	/**
	 *	Obtains the table name from field name
	 *
	 *	@param	string	$fieldName
	 *	@return	string
	 */
	protected function tableFromField(string $fieldName)
	{
		$result = parent::tableFromField($fieldName);

		if ($result != '')
		{
			return $result;
		}

		foreach ($this->joins as $name => $join)
		{
			$instance = $join['joinedTable']['instance'];
			//
			if ($instance->hasField($fieldName))
			{
				return $name;
			}
		}

		return '';
	}

	/**
	 *	Returns the order by clause
	 *
	 *	@return	\Collei\Database\Query\Clauses\OrderBy
	 */
	protected function getOrderByClause()
	{
		if (!is_null($this->order))
		{
			return $this->order->asArray();
		}
		//
		return null;
	}

	/**
	 *	Performs joins
	 *
	 *	@param	string	$tableName
	 *	@param	int		$kind
	 *	@return	\Collei\Database\Query\Clauses\Join
	 */
	protected function joiner(string $tableName, int $kind)
	{
		$join = new Join($this, $tableName, $kind);
		//
		if (!DB::isNaive())
		{
			if (DS::hasTable($tableName))
			{
				$this->joins[$tableName] = [
					'join' => $join,
					'joinedTable' => [
						'name' => $tableName,
						'instance' => DS::getTable($tableName)
					]
				];
			}
			else
			{
				throw new DatabaseException(
					'Table is unavailable for querying: ' .
						$tableName .
						'. Please declare it or enable naive mode.'
				);
			}
		}
		else
		{
			$this->joins[$tableName] = [
				'join' => $join,
				'joinedTable' => [
					'name' => $tableName,
					'instance' => DS::getTable($tableName)
				]
			];
		}
		//
		return $join;
	} 

	/**
	 *	Builds and instantiates
	 *
	 *	@param	string	$tableName
	 */
	public function __construct(string $tableName)
	{
		parent::__construct();
		$this->initialize($tableName);
	}

	/**
	 *	Returns the query as string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		$joines = [];

		if (count($this->joins) > 0)
		{
			foreach ($this->joins as $n => $jo)
			{
				$joines[] = '' . $jo['join'] . '';
			}
		}

		$page = $this->page;
		$limit = null;
		$offset = null;

		if ($this->rowsPerPage > 0)
		{
			if ($page > 0)
			{
				$limit = $this->rowsPerPage;
				$offset = ($page - 1) * $limit;
			}
		}

//		if ($this->rowsPerPage > 0)
//		{
//			logit(__METHOD__, print_r([[$this->page,$this->rowsPerPage],[$limit,$offset]],true));
//		}

		$sqlo = $this->db_connection->dialect->select(
			$this->getTableName(),
			$this->getFields(),
			(!empty($joines) ? $joines : null),
			$this->where,
			null,
			null,
			$this->getOrderByClause() ?? [],
			$limit,
			$offset
		);

		//logit(__METHOD__, print_r(['sqlo' => $sqlo],true));

		return $sqlo;
	}

	/**
	 *	Adds fields to the Select result
	 *
	 *	@param	string	...$fields
	 *	@return	\Collei\Database\Query\Select
	 */
	public function select(string ...$fields)
	{
		$this->setFields($fields);
		return $this;
	}

	/**
	 *	Sets the number of rows per page
	 *
	 *	@param	int	$rowsPerPage
	 *	@return	\Collei\Database\Query\Select
	 */
	public function pageSize(int $rowsPerPage = null)
	{
		$rowsPerPage = $rowsPerPage ?? -1;
		$this->rowsPerPage = ($rowsPerPage < 1) ? -1 : $rowsPerPage;
		//
		return $this;
	}

	/**
	 *	Sets the page to retrieve
	 *
	 *	@param	int	$page
	 *	@return	\Collei\Database\Query\Select
	 */
	public function page(int $page = null)
	{
		$page = $page ?? 1;
		$this->page = ($page < 1) ? 1 : $page;
		//
		return $this;
	}

	/**
	 *	Adds and returns an INNER JOIN clause
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Clauses\Join
	 */
	public function join(string $tableName)
	{
		try
		{
			return $this->joiner($tableName, Join::INNER_JOIN);
		}
		catch (DatabaseException $ex)
		{
			throw new DatabaseException($ex->getMessage());
		}
	} 

	/**
	 *	Adds and returns a LEFT JOIN clause
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Clauses\Join
	 */
	public function leftJoin(string $tableName)
	{
		try
		{
			return $this->joiner($tableName, Join::LEFT_JOIN);
		}
		catch (DatabaseException $ex)
		{
			throw new DatabaseException($ex->getMessage());
		}
	} 

	/**
	 *	Adds and returns a RIGHT JOIN clause
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Clauses\Join
	 */
	public function rightJoin(string $tableName)
	{
		try
		{
			return $this->joiner($tableName, Join::RIGHT_JOIN);
		}
		catch (DatabaseException $ex)
		{
			throw new DatabaseException($ex->getMessage());
		}
	} 

	/**
	 *	Adds and returns a Where clause
	 *
	 *	@param	\Collei\Database\Query\Clauses\Where	$subClause
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function where(Where $subClause = null)
	{
		if (count($this->fields) == 0)
		{
			$this->setFields(['*']);
		}

		$clause = Where::new();
		$clause->setQueryInstance($this);

		if (!is_null($subClause))
		{
			$clause->setTableName($this->getTableName());
			$clause->setTableInstance($this->getTableInstance());
			$clause->addClause($subClause);
		}

		$this->where = $clause;
		return $clause;
	}

	/**
	 *	Adds and returns an Order By clause
	 *
	 *	@param	string	$fieldName
	 *	@return	\Collei\Database\Query\Clauses\OrderBy
	 */
	public function orderBy(string $fieldName = null, bool $desc = false)
	{
		$order = new OrderBy($this);
		$this->order = $order;
		//
		if (!is_null($fieldName))
		{
			if ($desc)
			{
				return $order->desc($fieldName);	
			}
			//
			return $order->asc($fieldName);	
		}
		//
		return $order;
	}

	/**
	 *	Runs the query and brings results, if any
	 *
	 *	@return	mixed
	 */
	public function gather(bool $asObject = false)
	{
		$rowset = '';
		$code_sql = '' . $this . '';
		//
		if (!is_null($this->db_connection))
		{
			$rowset = $this->db_connection->select($code_sql, []);

			if ($asObject)
			{
				$objArr = [];

				foreach ($rowset as $row)
				{
					$objArr[] = new class($row) {
						public function __construct($data) {
							foreach ($data as $n => $v) {
								if (is_string($n)) {
									$this->$n = $v;
								}
							}
						}
					};
				}

				return $objArr;
			}
		}
		//
		return $rowset;
	}

	/**
	 *	Runs the query and brings results as the specified Model instances, if any
	 *
	 *	@param	string	$modelClass
	 *	@return	\Collei\Database\Yanfei\ModelResult
	 *	@throws InvalidArgumentException
	 */
	public function gatherAs(string $modelClass)
	{
		if (!is_a($modelClass, Model::class, true))
		{
			throw new InvalidArgumentException(
				$modelClass . ' is not a subclass of ' . Model::class
			);
		}

		$rowset = $this->gather();

		$resultSet = new ModelResult($modelClass);

		foreach ($rowset as $row)
		{
			$resultSet->add($modelClass::fill($row));
		}

		return $resultSet;
	}

	/**
	 *	Runs the query and brings results, if any
	 *
	 *	@return	mixed
	 */
	public function execute()
	{
		return $this->gather();
	}

}


