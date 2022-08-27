<?php
namespace Collei\Database\Query\Clauses;

use Collei\Utils\Values\Value;
use Collei\Utils\Calendar\Date;
use Collei\Database\Query\Query;
use Collei\Database\Query\Select;
use Collei\Database\Yanfei\Model;
use Collei\Database\Box\QueryBox;
use Closure;
use RuntimeException;
use InvalidArgumentException;

/**
 *	Embodies the expressions inside the Where clause
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class Where extends QueryBox
{
	/**
	 *	@var \Collei\Database\Query\Clauses\Where
	 */
	private $query;

	/**
	 *	@var array
	 */
	private $clause = [];

	/**
	 *	@var string
	 */
	private $junction = 'OR';

	/**
	 *
	 */
	public const OPERATOR_TABLE = [
		'=' => 'is',
		'<>' => 'isNot',
		'!=' => 'isNot',
		'like' => 'like',
		'not like' => 'notLike',
		'!like' => 'notLike',
		'in' => 'in',
		'not in' => 'notIn',
		'!in' => 'notIn',
		'<' => 'lessThan',
		'<=' => 'lessOrEqual',
		'>' => 'greaterThan',
		'>=' => 'greaterOrEqual',
	];


	/**
	 *	Defines the current junctor to be used
	 *
	 *	@param	string	$junctor
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	private function junction(string $junctor)
	{
		$this->junction = $junctor;
		return $this;
	}

	/**
	 *	Applies the current junctor
	 *
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	private function applyJunctor()
	{
		$counter = count($this->clause);
		//
		if ($counter > 0) {
			if ($this->clause[$counter - 1] !== $this->junction) {
				$this->clause[] = $this->junction;
			}
		}
		//
		return $this;
	}

	/**
	 *	Transforms the value to be used in the query
	 *
	 *	@param	mixed	$value
	 *	@return	string
	 */
	private function adaptValue($value)
	{
		$type = Value::prospectType($value);
		$val = '';
		//
		if (
			$type == Value::TYPE_STRING ||
			strpos($value, "'") !== false ||
			strpos($value, '--') !== false
		) {
			return ("'" . str_replace(["'",'--'], ["\\'",''], $value) . "'");
		}
		//
		if ($type == Value::TYPE_DATE) {
			return ("'" . date('Y-m-d H:i:s', Date::toDate($value)) . "'");
		}
		return ''. $value . ''; 
	}

	/**
	 *	Initializes the instance
	 *
	 *	@param	\Collei\Database\Query\Query	$query
	 */
	protected function __construct(Query $query = null)
	{
		$this->query = $query;
	}

	/**
	 *	Adds another Where clause
	 *
	 *	@param	\Collei\Database\Query\Clauses\Where	$clause
	 *	@return	void
	 */
	protected function addClause(Where $clause)
	{
		$this->clause[] = $clause->clause;
	}

	/**
	 *	Sets the related query instance
	 *
	 *	@param	\Collei\Database\Query\Query	$query
	 *	@return	void
	 */
	protected function setQueryInstance(Query $query)
	{
		$this->query = $query;
	}

	/**
	 *	Returns a brand new instance
	 *
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public static function new()
	{
		return new Where();
	}

	/**
	 *	Returns a brand new instance
	 *
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public static function createWith(Query $query)
	{
		$where = new Where();
		$where->setQueryInstance($query);
		$query->setWhereClause($where);
		//
		return $where;
	}

	/**
	 *	Adds first Where clause to the chain
	 *
	 *	@param	mixed	$left = null
	 *	@param	mixed	$middle = null
	 *	@param	mixed	$right = null
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function where($left = null, $middle = null, $right = null)
	{
		if ($left instanceof Where) {
			$this->addClause($left);
			return $this;
		}
		//
		if (is_string($left) && !is_null($middle)) {
			if (is_null($right)) {
				return $this->is($left, $middle);
			}
			//
			if (is_string($middle)) {
				$middle = strtolower($middle);
				$method = Where::OPERATOR_TABLE[$middle] ?? '';
				//
				if (!empty($method)) {
					if (in_array($method, ['in','notIn'])) {
						if (!is_array($right)) {
							$right = array($right);
						}
					}
					//
					return $this->$method($left, $right);
				}
			} 
		}
		//
		return $this;
	}

	/**
	 *	Adds another Where clause to the chain
	 *
	 *	@param	mixed	$left = null
	 *	@param	mixed	$middle = null
	 *	@param	mixed	$right = null
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function andWhere($left = null, $middle = null, $right = null)
	{
		if ($left instanceof Where) {
			$this->applyJunctor();
			$this->addClause($left);
			return $this;
		}
		//
		if (is_null($left) && is_null($middle) && is_null($right)) {
			return $this->and();
		}
		//
		return $this->and()->where($left, $middle, $right);
	}

	/**
	 *	Adds another Where clause to the chain
	 *
	 *	@param	mixed	$left = null
	 *	@param	mixed	$middle = null
	 *	@param	mixed	$right = null
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function orWhere($left = null, $middle = null, $right = null)
	{
		if ($left instanceof Where) {
			$this->applyJunctor();
			$this->addClause($left);
			return $this;
		}
		//
		if (is_null($left) && is_null($middle) && is_null($right)) {
			return $this->or();
		}
		//
		return $this->or()->where($left, $middle, $right);
	}

	/**
	 *	Adds the AND junction
	 *
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function and()
	{
		return $this->junction('AND')->applyJunctor();
	}

	/**
	 *	Adds the OR junction
	 *
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function or()
	{
		return $this->junction('OR')->applyJunctor();
	}

	/**
	 *	Adds '=' comparison
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function is(string $field, $value)
	{
		$this->applyJunctor();
		$this->clause[] = [ $field, '=', $this->adaptValue($value) ];
		return $this;
	}

	/**
	 *	Adds '<>' comparison
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function isNot(string $field, $value)
	{
		$this->applyJunctor();
		$this->clause[] = [ $field, '<>', $this->adaptValue($value) ];
		return $this;
	}

	/**
	 *	Adds 'LIKE' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function like(string $field, $value)
	{
		$this->applyJunctor();
		$this->clause[] = [ $field, 'LIKE', $this->adaptValue('%' . $value . '%') ];
		return $this;
	}

	/**
	 *	Adds 'NOT LIKE' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function notLike(string $field, $value)
	{
		$this->applyJunctor();
		$this->clause[] = [ $field, 'NOT LIKE', $this->adaptValue('%' . $value . '%') ];
		return $this;
	}

	/**
	 *	Adds 'IN' operation
	 *
	 *	@param	string	$field
	 *	@param	array	$values
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function in(string $field, array $values)
	{
		$this->applyJunctor();
		$adaptedValues = [];
		foreach ($values as $value)
		{
			$adaptedValues[] = $this->adaptValue($value);
		}
		$this->clause[] = [ $field, 'IN', $adaptedValues ];
		return $this;
	}

	/**
	 *	Adds 'NOT IN' operation
	 *
	 *	@param	string	$field
	 *	@param	array	$values
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function notIn(string $field, array $values)
	{
		$this->applyJunctor();
		$adaptedValues = [];
		foreach ($values as $value)
		{
			$adaptedValues[] = $this->adaptValue($value);
		}
		$this->clause[] = [ $field, 'NOT IN', $adaptedValues ];
		return $this;
	}

	/**
	 *	Adds '<' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function lessThan(string $field, $value)
	{
		$this->applyJunctor();
		$this->clause[] = [ $field, '<', $this->adaptValue($value) ];
		return $this;
	}

	/**
	 *	Adds '<=' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function lessOrEqual(string $field, $value)
	{
		$this->applyJunctor();
		$this->clause[] = [ $field, '<=', $this->adaptValue($value) ];
		return $this;
	}

	/**
	 *	Adds '>' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function greaterThan(string $field, $value)
	{
		$this->applyJunctor();
		$this->clause[] = [ $field, '>', $this->adaptValue($value) ];
		return $this;
	}

	/**
	 *	Adds '>=' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function greaterOrEqual(string $field, $value)
	{
		$this->applyJunctor();
		$this->clause[] = [ $field, '>=', $this->adaptValue($value) ];
		return $this;
	}

	/**
	 *	Adds '<=' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function lte(string $field, $value)
	{
		return $this->lessOrEqual($field, $value);
	}

	/**
	 *	Adds '>=' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function gte(string $field, $value)
	{
		return $this->greaterOrEqual($field, $value);
	}

	/**
	 *	Adds '<' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function lt(string $field, $value)
	{
		return $this->lessThan($field, $value);
	}

	/**
	 *	Adds '>' operation
	 *
	 *	@param	string	$field
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function gt(string $field, $value)
	{
		return $this->greaterThan($field, $value);
	}

	/**
	 *	Adds order by clause
	 *
	 *	@param	string	$fieldName
	 *	@return	\Collei\Database\Query\Clauses\OrderBy|bool
	 */
	public function orderBy(string $fieldName = null)
	{
		if ($this->query instanceof Select)
		{
			return $this->query->orderBy($fieldName);
		}
		return false;
	}

	/**
	 *	Returns the clause as SQL string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		$semicode = [];
		foreach ($this->clause as $piece)
		{
			if ($piece instanceof Where)
			{
				$semicode[] = '(' . $piece . ')';
			}
			elseif (is_array($piece))
			{
				foreach ($piece as $pn => $pv)
				{
					if (is_array($pv))
					{
						if (count($pv) == 3 && in_array($pv[1], ['IN','NOT IN']))
						{
							$piece[$pn][2] = '(' . implode(', ', $piece[$pn][2]) . ')';
						}
						$piece[$pn] = '(' . implode(' ', $piece[$pn]) . ')';
					}
				}
				$semicode[] = '(' . implode(' ', $piece) . ')';
			}
		}
		$code_where = implode(' '.$this->junction.' ', $semicode);
		return $code_where;
	}

	/**
	 *	Runs the query and returns results
	 *
	 *	@param	bool	$asObject
	 *	@return	mixed
	 */
	public function gather(bool $asObject = false)
	{
		if ($this->query instanceof Select)
		{
			return $this->query->gather($asObject);
		}
		else
		{
			return $this->query->execute();
		}
	}

	/**
	 *	Runs the query and brings results as the specified Model instances, if any
	 *
	 *	@param	string	$modelClass
	 *	@return	\Collei\Database\Yanfei\ModelResult
	 *	@throws RuntimeException, InvalidArgumentException
	 */
	public function gatherAs(string $modelClass)
	{
		$result = null;

		if (!($this->query instanceof Select))
		{
			throw new RuntimeException('Calls to gatherAs() method is only valid for Select queries !');
		}

		if (!is_a($modelClass, Model::class, true))
		{
			throw new InvalidArgumentException($modelClass . ' is not a subclass of ' . Model::class);
		}

		return $this->query->gatherAs($modelClass);
	}

	/**
	 *	Runs the query
	 *
	 *	@return	mixed
	 */
	public function execute()
	{
		return $this->query->execute();
	}

}


