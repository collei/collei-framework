<?php
namespace Collei\Database\Relations;

use Closure;

/**
 *	Embodies basic relation tasks and features
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
abstract class Relation
{
	/**
	 *	@var \Collei\Database\Yanfei\Model $left
	 */
	protected $left = null;

	/**
	 *	@var \Collei\Database\Yanfei\Model $right
	 */
	protected $right = null;

	/**
	 *	@var string $leftKey
	 */
	protected $leftKey = '';

	/**
	 *	@var string $rightKey
	 */
	protected $rightKey = '';

	/**
	 *	Tries to infer the keys of the involved left and right tables
	 *
	 *	@param	string	$left
	 *	@param	string	$right
	 *	@return void	
	 */
	protected function inferKeys(string $left = null, string $right = null)
	{
		$this->leftKey = $left ?? $this->left->getKey();
		$this->rightKey = $right ?? $this->left->getEntity() . '_id';
	}

	/**
	 *	Returns the data from the relation results
	 *
	 *	@abstract
	 *	@return	mixed
	 */
	abstract protected function fetchData();

	/**
	 *	Builds and instantiates
	 *
	 */
	public function __construct()
	{
	}

	/**
	 *	Fetch the resulting data from relatrion
	 *
	 *	@param	\Closure	$transform
	 *	@return	mixed
	 */
	public function fetch(Closure $transform = null)
	{
		$data = $this->fetchData();

		if (!is_null($transform) and is_callable($transform))
		{
			return $transform($data);
		}
		return $data;
	}

}


