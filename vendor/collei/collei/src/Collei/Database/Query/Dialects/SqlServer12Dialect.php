<?php
namespace Collei\Database\Query\Dialects;

use Collei\Database\Query\Dialects\SqlServerDialect;
use Collei\Utils\Arr;

/**
 *	Specifies the basic query structs for SQL Server 12 (and onwards) databases
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-12-06
 */
class SqlServer12Dialect extends SqlServerDialect
{
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
		$sql = ' SELECT ' . 
			(empty($selectFields) ? '*' : Arr::join(',', $selectFields)) .
			' FROM ' . $fromTable . ' ' .
			(empty($joins) ? '' : Arr::join('', $joins)) .
			(empty($where) ? '' : (' WHERE ' . $where)) .
			(empty($groupBy) ? '' : (' GROUP BY ' . Arr::join(', ', $groupBy))) .
			(empty($having) ? '' : (' HAVING ' . $having));

		if (!empty($orderBy))
		{
			$sql .= (' ORDER BY ' . Arr::join(', ', $orderBy));

			if (!empty($limit))
			{
				$sql .= '' .
					(empty($offset) ? '' : (' OFFSET ' . $offset . ' ROWS ')) .
					(' FETCH NEXT ' . $limit . ' ROWS ONLY ');
			}
		}

		return $sql;
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
		$sql = ' INSERT INTO ' . $into .
			' (' . Arr::join(', ', $fields) . ') ' .
			' SELECT ' . 
			(empty($selectFields) ? '*' : Arr::join(',', $selectFields)) .
			' FROM ' . $fromTable . ' ' .
			(empty($joins) ? '' : Arr::join('', $joins)) .
			(empty($where) ? '' : (' WHERE ' . $where)) .
			(empty($groupBy) ? '' : (' GROUP BY ' . Arr::join(', ', $groupBy))) .
			(empty($having) ? '' : (' HAVING ' . $having));

		if (!empty($orderBy))
		{
			$sql .= (' ORDER BY ' . Arr::join(', ', $orderBy));
			
			if (!empty($limit))
			{
				$sql .= '' .
					(empty($offset) ? '' : (' OFFSET ' . $offset . ' ROWS ')) .
					(' FETCH NEXT ' . $limit . ' ROWS ONLY ');
			}
		} 

		return $sql;
	}

}

