<?php
namespace Collei\Database\Query;

use Closure;
use Collei\Database\Box\DataBox;
use Collei\Database\Meta\DS;
use Collei\Database\Yanfei\Model;
use Collei\Database\Query\Select;
use Collei\Database\Query\Insert;
use Collei\Database\Query\Update;
use Collei\Database\Query\Delete;
use Collei\Database\Query\Clauses\Where;
use Collei\Database\DatabaseException;
use Collei\Support\Arr;

/**
 *	Embodies basic query helper starters
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class DB extends DataBox 
{
	/**
	 *	@var bool $naive_mode
	 */
	private static $naive_mode = true;

	/**
	 *	Set the naive mode for the CDQE - Collei Database Query Engine 
	 *
	 *	@param	bool	$value
	 *	@return	void
	 */
	public static function setNaive(bool $value)
	{
		self::$naive_mode = $value;
	}

	/**
	 *	Returns if the CDQE - Collei Database Query Engine is in naive mode 
	 *
	 *	@return	bool
	 */
	public static function isNaive()
	{
		return self::$naive_mode;
	}

	/**
	 *	Returns the Select query instance
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Select
	 */
	public static function from(string $tableName)
	{
		if (!self::isNaive()) {
			if (!DS::hasTable($tableName)) {
				throw new DatabaseException(
					'Table is unavailable for querying: '
					. $tableName
					. '. Please declare it or enable naive mode.'
				);
			} else {
				return new Select($tableName);
			}
		}
		//
		return new Select($tableName);
	}

	/**
	 *	Returns Where clause
	 *
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public static function where()
	{
		return Where::new();
	}

	/**
	 *	Returns Insert instance
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Insert
	 */
	public static function into(string $tableName)
	{
		return Insert::into($tableName);
	}

	/**
	 *	Returns Update instance
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Update
	 */
	public static function update(string $tableName)
	{
		return Update::make($tableName);
	}

	/**
	 *	Performs Delete instance
	 *
	 *	@param	string	$tableName
	 *	@return	\Collei\Database\Query\Delete
	 */
	public static function delete(string $tableName)
	{
		return Delete::make($tableName);
	}

}


