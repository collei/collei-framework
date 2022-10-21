<?php
namespace Collei\Database\Yanfei;

use InvalidArgumentException;
use Collei\Database\DatabaseException;
use Collei\Database\Meta\DS;
use Collei\Database\Meta\Table;
use Collei\Database\Yanfei\Model;
use Collei\Database\Query\DB;
use Collei\Database\Query\Select;
use Collei\Database\Query\Clauses\Where;
use Collei\Support\Arr;
use Collei\Support\Str;

/**
 *	Encapsulates associative models
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
abstract class AssociativeModel extends Model
{
	/**
	 *	Returns the first associated model, if any
	 *
	 *	@return	\Collei\Database\Yanfei\Model
	 */
	protected function first()
	{
		$list = $this->associates ?? [];
		//
		if (count($list) < 1) {
			throw new DatabaseException('Undefined first associate for the associative model ' . get_class($this));
		}
		//
		return $list[0];
	}

	/**
	 *	Returns the second associated model, if any
	 *
	 *	@return	\Collei\Database\Yanfei\Model
	 */
	protected function second()
	{
		$list = $this->associates ?? [];
		//
		if (count($list) < 2) {
			throw new DatabaseException('Undefined second associate for the associative model ' . get_class($this));
		}
		//
		return $list[1];
	}

	/**
	 *	Returns the third associated model, if any
	 *
	 *	@return	\Collei\Database\Yanfei\Model
	 */
	protected function third()
	{
		$list = $this->associates ?? [];
		//
		if (count($list) < 3) {
			throw new DatabaseException('Undefined third associate for the associative model ' . get_class($this));
		}
		//
		return $list[2];
	}

	/**
	 *	Returns the ($index-1)-th associated model, if any
	 *
	 *	@return	\Collei\Database\Yanfei\Model
	 */
	protected function further(int $index)
	{
		$list = $this->associates ?? [];
		//
		if (count($list) < $index) {
			throw new DatabaseException('Undefined associate #' . $index . ' for the associative model ' . get_class($this));
		}
		//
		return $list[$index - 1];
	}

}


