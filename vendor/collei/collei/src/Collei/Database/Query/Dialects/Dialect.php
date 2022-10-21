<?php
namespace Collei\Database\Query\Dialects;

use Collei\Support\Arr;
use Collei\Support\Str;

/**
 *	Specifies the basic query structs for certain database brands
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
abstract class Dialect
{
	/**
	 *	@var array $functions
	 */
	protected $functions = [
		'current_timestamp' => 'current_timestamp()'
	];

	/**
	 *	Returns the corresponding SQL function
	 *
	 *	@param	string	$function
	 *	@return	string	
	 */
	public final function functions(string $function)
	{
		if (array_key_exists($function, $this->functions)) {
			return $this->functions[$function];
		}
		//
		return '';
	}

	/**
	 *	@var array $dataTypes
	 */
	protected $dataTypes = [
		'int' => 'int',
		'string' => 'varchar',
		'float' => 'double',
		'real' => 'double',
		'double' => 'double',
		'text' => 'varchar',
	];

	/**
	 *	Returns the corresponding SQL data type
	 *
	 *	@param	string	$dataTypes
	 *	@return	string	
	 */
	public final function dataTypes(
		string $dataType, int $length = null, int $precision = null
	) {
		if (array_key_exists($dataType, $this->dataTypes)) {
			$details = '';
			//
			if (Str::in($dataType, ['string','text'])) {
				$details = (empty($length) ? '50' : '');
			} elseif (
				Str::in($dataType, ['float','real','double','decimal','currency'])
			) {
				$details = (
					empty($length)
						? '' 
						: $length . (empty($precision) ? '' : (',' . $precision))
				);
			}
			//
			return $this->dataTypes[$dataType] . (
				empty($details) ? '' : ('(' . $details . ')')
			);
		}
		return '';
	}

	/**
	 *	Outlines the general, simpler select query structure 
	 *
	 *	@param	string	$fromTable
	 *	@param	array	$selectFields
	 *	@param	array	$joins
	 *	@param	string	$where
	 *	@param	array	$groupBy
	 *	@param	string	$having
	 *	@param	array	$orderBy
	 *	@param	string	$limit
	 *	@param	string	$offset
	 *	@return	string
	 */
	public function select(
		string $fromTable, array $selectFields = null,
		array $joins = null, string $where = null,
		array $groupBy = null, string $having = null,
		array $orderBy = null, string $limit = null, string $offset = null
	){
		return ' SELECT ' . 
			(empty($selectFields) ? '*' : Arr::join(',', $selectFields)) .
			' FROM ' . $fromTable . ' ' .
			(empty($joins) ? '' : Arr::join('', $joins)) .
			(empty($where) ? '' : (' WHERE ' . $where)) .
			(empty($groupBy) ? '' : (' GROUP BY ' . Arr::join(', ', $groupBy))) .
			(empty($having) ? '' : (' HAVING ' . $having)) .
			(empty($orderBy) ? '' : (' ORDER BY ' . Arr::join(', ', $orderBy))) .
			(empty($offset) ? '' : (' OFFSET ' . $offset)) .
			(empty($limit) ? '' : (' LIMIT ' . $limit)) . ';';
	}

	/**
	 *	Outlines the general, simpler insert into structure 
	 *
	 *	@param	string	$into
	 *	@param	array	$fields
	 *	@param	array	$values
	 *	@return	string
	 */
	public function insertOnly(string $into, array $fields, array $values)
	{
		return ' INSERT INTO ' . $into .
			' (' . Arr::join(', ', $fields) . ') ' .
			' VALUES (' . Arr::join(', ', $values) . '); ';
	}
	
	/**
	 *	Outlines the general, simpler insert into...select from structure 
	 *
	 *	@param	string	$into
	 *	@param	array	$fields
	 *	@param	string	$fromTable
	 *	@param	array	$selectFields
	 *	@param	array	$joins
	 *	@param	string	$where
	 *	@param	array	$groupBy
	 *	@param	string	$having
	 *	@param	array	$orderBy
	 *	@param	string	$limit
	 *	@param	string	$offset
	 *	@return	string
	 */
	public function insertSelect(
		string $into, array $fields = null, string $fromTable = '',
		array $selectFields = null, array $joins = null,
		string $where = null, array $groupBy = null,
		string $having = null, array $orderBy = null,
		string $limit = null, string $offset = null
	){
		return ' INSERT INTO ' . $into .
			' (' . Arr::join(', ', $fields) . ') ' .
			' SELECT ' . 
			(empty($selectFields) ? '*' : Arr::join(',', $selectFields)) .
			' FROM ' . $fromTable . ' ' .
			(empty($joins) ? '' : Arr::join('', $joins)) .
			(empty($where) ? '' : (' WHERE ' . $where)) .
			(empty($where) ? '' : (' GROUP BY ' . Arr::join(', ', $groupBy))) .
			(empty($having) ? '' : (' HAVING ' . $having)) .
			(empty($orderBy) ? '' : (' ORDER BY ' . Arr::join(', ', $orderBy))) .
			(empty($offset) ? '' : (' OFFSET ' . $offset)) .
			(empty($limit) ? '' : (' LIMIT ' . $limit)) . ';';
	}

	/**
	 *	Outlines the general, simpler delete from structure 
	 *
	 *	@param	string	$from
	 *	@param	array	$where
	 *	@return	string
	 */
	public function delete(string $from, string $where)
	{
		return ' DELETE FROM ' . $from . ' WHERE ' . $where . '; ';
	}
	
	/**
	 *	Outlines the general, simpler update from structure 
	 *
	 *	@param	string	$table
	 *	@param	array	$setFields
	 *	@param	string	$where
	 *	@return	string
	 */
	public function update(
		string $table, array $setFields, string $where = null
	) {
		return ' UPDATE ' . $table .
			' SET ' . Arr::joinKeyValueHolders(',', $setFields, function($n, $v){ return " $n = $v"; }) .
			(empty($where) ? '' : (' WHERE ' . $where)) . ';';
	}


	/**
	 *	Outlines the general, simpler create table structure
	 *
	 *	@param	string	$table
	 *	@param	array	$fields
	 *	@param	mixed	$primaryKey
	 *	@param	array	$foreignKeys
	 */
	public function createTable(
		string $table,
		array $fields,
		$primaryKey = null,
		array $foreignKeys = null
	) {
		return '';
	}

}



