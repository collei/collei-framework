<?php
namespace Collei\Database\Box;

use Collei\Database\Box\SelectClause;
use Collei\Database\Meta\Table;
use Collei\Database\Query\DB;
use Collei\Database\Query\Query;
use Collei\Database\Query\Select;
use Collei\Database\Query\Clauses\Where;
use Collei\Database\Query\Dialects\Dialect;
use Collei\Exceptions\MethodNotImplementedException;

/**
 *	Encapsulates some features shared across queries
 *
 *	@author	<alarido.su@gmail.com>
 *	@since	2021-07-xx
 */
abstract class QueryBox
{
	/**
	 *	@var string $table_name
	 */
	private $table_name = '';

	/**
	 *	@var \Collei\Database\Meta\Table $table_instance
	 */
	private $table_instance = null;

	/**
	 *	@var \Collei\Database\Query\Dialects\Dialect $dialect
	 */
	private $dialect = null;

	/**
	 *	Builds a new instance
	 *
	 */
	public function __construct()
	{
	}

	/**
	 *	Defines name of the target table
	 *
	 *	@param	string $tableName
	 *	@return	string
	 */
	protected final function setTableName(string $tableName)
	{
		$this->table_name = $tableName;
		return $tableName;
	}

	/**
	 *	Returns the name of the target table
	 *
	 *	@return	string
	 */
	protected final function getTableName()
	{
		return $this->table_name;
	}

	/**
	 *	Defines the instance of the target table
	 *
	 *	@param	\Collei\Database\Meta\Table $instance
	 *	@return	\Collei\Database\Meta\Table
	 */
	protected final function setTableInstance(Table $instance = null)
	{
		$this->table_instance = $instance;
		return $instance;
	}

	/**
	 *	Returns the instance of the target table
	 *
	 *	@return	\Collei\Database\Meta\Table
	 */
	protected final function getTableInstance()
	{
		return $this->table_instance;
	}

	/**
	 *	Defines the dialect of the query
	 *
	 *	@param	\Collei\Database\Query\Dialects\Dialect $tableName
	 *	@return	\Collei\Database\Query\Dialects\Dialect
	 */
	protected final function setDialect(Dialect $dialect = null)
	{
		$this->dialect = $dialect;
		return $dialect;
	}

	/**
	 *	Returns the dialect of the query
	 *
	 *	@return	\Collei\Database\Query\Dialects\Dialect
	 */
	protected final function getDialect()
	{
		return $this->dialect;
	}

	/**
	 *	Returns table name from the specified field
	 *
	 *	@param	string $fieldName
	 *	@return	string
	 */
	protected function tableFromField(string $fieldName)
	{
		if (!is_null($this->table_instance))
		{
			if ($this->table_instance->hasField($fieldName))
			{
				return $this->table_name;
			}
		}
		return '';
	}

	/**
	 *	Adds a where clause
	 *
	 *	@param	\Collei\Database\Query\Clauses\Where $clause
	 *	@return	void
	 */
	protected function addClause(Where $clause)
	{
		throw new MethodNotImplementedException('Method ' . __METHOD__ . ' not implemented by class ' . get_class($this));
	}

	/**
	 *	Returns the query fields
	 *
	 *	@return	array
	 */
	protected function getFields()
	{
		throw new MethodNotImplementedException('Method ' . __METHOD__ . ' not implemented by class ' . get_class($this));
	}

	/**
	 *	Defines the query fields
	 *
	 *	@param	array	$fields
	 *	@return	void
	 */
	protected function setFields(array $fields)
	{
		throw new MethodNotImplementedException('Method ' . __METHOD__ . ' not implemented by class ' . get_class($this));
	}

	/**
	 *	Returns if the field exists
	 *
	 *	@param	string	$fieldName
	 *	@return	bool
	 */
	protected function hasField(string $fieldName)
	{
		throw new MethodNotImplementedException('Method ' . __METHOD__ . ' not implemented by class ' . get_class($this));
	}

	/**
	 *	Assigns a Query instance to this query
	 *
	 *	@param	\Collei\Database\Query\Query	$query
	 *	@return	void
	 */
	protected function setQueryInstance(Query $query)
	{
		throw new MethodNotImplementedException('Method ' . __METHOD__ . ' not implemented by class ' . get_class($this));
	}

	/**
	 *	Assigns a Where instance to a Select, Delete or Update query
	 *
	 *	@param	\Collei\Database\Query\Where	$where
	 *	@return	void
	 */
	protected function setWhereClause(Where $where)
	{
		throw new MethodNotImplementedException('Method ' . __METHOD__ . ' not implemented by class ' . get_class($this));
	}

	/**
	 *	Returns the order by clause
	 *
	 *	@return	\Collei\Database\Query\Clauses\OrderBy
	 */
	protected function getOrderByClause()
	{
		throw new MethodNotImplementedException('Method ' . __METHOD__ . ' not implemented by class ' . get_class($this));
	}

}


