<?php
namespace Collei\Database\Query\Clauses;

use Collei\Database\Query\Select;

/**
 *	Encapsulates the features and properties of Order By clause
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class OrderBy
{
	/**
	 *	@var \Collei\Database\Query\Select
	 */
	private $select;

	/**
	 *	@var array
	 */
	private $fields = [];

	/**
	 *	Creates a new instance of the clause
	 *
	 *	@param	\Collei\Database\Query\Select	$select
	 */
	public function __construct(Select $select)
	{
		$this->select = $select;
	}

	/**
	 *	Converts to string format
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		return 'ORDER BY ' . implode(', ', $this->asArray());
	}

	/**
	 *	Returns as array
	 *
	 *	@return	array
	 */
	public function asArray()
	{
		$orders = [];
		foreach ($this->fields as $n => $v)
		{
			$orders[] = $n . ' ' . $v;
		}

		return $orders;
	}

	/**
	 *	Ascending order by $fieldName
	 *
	 *	@param	string	$fieldName
	 *	@return	\Collei\Database\Query\Clauses\OrderBy
	 */
	public function asc(string $fieldName)
	{
		$this->fields[$fieldName] = 'ASC';
		return $this;
	}

	/**
	 *	Descending order by $fieldName
	 *
	 *	@param	string	$fieldName
	 *	@return	\Collei\Database\Query\Clauses\OrderBy
	 */
	public function desc(string $fieldName)
	{
		$this->fields[$fieldName] = 'DESC';
		return $this;
	}

	/**
	 *	Executes the query and returns results
	 *
	 *	@param	bool	$asObject
	 *	@return	mixed
	 */
	public function gather(bool $asObject = false)
	{
		return $this->select->gather($asObject);
	}

}


