<?php
namespace Collei\Database\Query\Clauses;

use Collei\Database\Box\QueryBox;
use Collei\Database\Query\Select;
use Collei\Database\Query\DB;
use Collei\Exceptions\Query\DatabaseQueryException;

/**
 *	Encapsulates attributes and contents of Join clauses
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class Join extends QueryBox
{
	/**
	 *	join constants
	 */
	public const RIGHT_JOIN = 1;
	public const LEFT_JOIN = 2;
	public const INNER_JOIN = 3;

	/**
	 *	join kind constants
	 */
	public const JOIN_KINDS = [
		self::RIGHT_JOIN => 'RIGHT JOIN',
		self::LEFT_JOIN => 'LEFT JOIN',
		self::INNER_JOIN => 'INNER JOIN'
	];

	/**
	 *	@var \Collei\Database\Query\Select $select
	 */
	private $select;

	/**
	 *	@var string $joined_table_name
	 */
	private $joined_table_name;

	/**
	 *	@var int $kind
	 */
	private $kind = self::INNER_JOIN;

	/**
	 *	@var array $keys
	 */
	private $keys = [];

	/**
	 *	Matches fields and tables
	 *
	 *	@param	string	$field
	 *	@param	string	&$fieldName
	 *	@param	string	&$tableName
	 *	@return	bool
	 */
	private function matcher(string $field, string &$fieldName, string &$tableName)
	{
		$regex_field = '#(\w+)#';
		$regex_table_dot_field = '#(\w+)\.(\w+)#';
		$matches = [];

		if (preg_match($regex_table_dot_field, $field, $matches) === 1)
		{
			$fieldName = $matches[2];
			$tableName = $matches[1];
			return true;
		}
		elseif (preg_match($regex_field, $field, $matches) === 1)
		{
			$fieldName = $field;
			$tableName = '';
			return true;
		}

		return false;
	}

	/**
	 *	Ask tables for the specified key
	 *
	 *	@param	string	$joinKey
	 *	@return	array
	 */
	private function askTablesFor(string $joinKey)
	{
		$table_name = '';
		$field_name = '';

		if ($this->matcher($joinKey, $field_name, $table_name))
		{
			if ($table_name == '')
			{
				$table_name = $this->select->tableFromField($field_name);
			}
		}

		return [
			'table' => $table_name,
			'field' => $field_name
		];
	}

	/**
	 *	Initializes a new Join clause
	 *
	 *	@param	\Collei\Database\Query\Select	$select
	 *	@param	string	$joinedTable
	 *	@param	int		$kind
	 */
	public function __construct(Select $select, string $joinedTable, int $kind = self::INNER_JOIN)
	{
		$this->select = $select;
		$this->joined_table_name = $joinedTable;
	}

	/**
	 *	Returns the string format
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		$ands = [];

		foreach ($this->keys as $joinCond)
		{
			$ands[] = implode(' = ', $joinCond);
		}

		return self::JOIN_KINDS[$this->kind] . ' ' . $this->joined_table_name . ' ON (' . implode(') AND (', $ands) . ') ';
	}

	/**
	 *	Inserts a key junction for the current Join
	 *
	 *	@param	string	$leftKey
	 *	@param	string	$rightKey
	 *	@return	\Collei\Database\Query\Clauses\Join
	 */
	public function on(string $leftKey, string $rightKey)
	{
		$left = $this->askTablesFor($leftKey);
		$right = $this->askTablesFor($rightKey);

		if (!DB::isNaive())
		{
			$msg = 'was not found in any table involved in the query. Please declare '
				. 'the table containing such field in database.php and join it to the '
				. 'query. Alternatively, you can also enable naive mode.';
			//
			if ($left[0] == '') 
			{
				throw new DatabaseQueryException("Field {$leftKey} {$msg}");
			} 
			if ($right[0] == '') 
			{
				throw new DatabaseQueryException("Field {$rightKey} {$msg}");
			} 
		}

		$this->keys[] = [
			'left' => $left['table'] . '.' . $left['field'],
			'right' => $right['table'] . '.' . $right['field']
		];

		//return $this;
		return $this->select;
	}

	/**
	 *	Adds a junction for the current Join
	 *
	 *	@param	string	$leftKey
	 *	@param	string	$rightKey
	 *	@return	\Collei\Database\Query\Clauses\Join
	 */
	public function and(string $leftKey, string $rightKey)
	{
		return $this->on($leftKey, $rightKey);
	}

	/**
	 *	Adds another table to the join as inner join
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Query
	 */
	public function join(string $tableName = null)
	{
		return $this->select->join($tableName);
	}

	/**
	 *	Adds another table to the join as left join
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Query
	 */
	public function leftJoin(string $tableName = null)
	{
		return $this->select->leftJoin($tableName);
	}

	/**
	 *	Adds another table to the join as right join
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Query
	 */
	public function rightJoin(string $tableName = null)
	{
		return $this->select->rightJoin($tableName);
	}

	/**
	 *	Adds a where clause
	 *
	 *	@param	\Collei\Database\Query\Clauses\Where	$subClause
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function where(Where $subClause = null)
	{
		return $this->select->where($subClause);
	}

	/**
	 *	Adds order by constraints
	 *
	 *	@param	string	$fieldName
	 *	@return	\Collei\Database\Query\Clauses\OrderBy
	 */
	public function orderBy(string $fieldName = null)
	{
		return $this->select->orderBy($fieldName);
	}

	/**
	 *	Returns the query results
	 *
	 *	@param	bool	$asObject
	 *	@return	array
	 */
	public function gather(bool $asObject = false)
	{
		return $this->select->gather($asObject);
	}

}

