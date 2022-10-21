<?php
namespace Collei\Database\Relations;

use Collei\Database\Yanfei\Model;
use Collei\Database\Query\DB;
use Collei\Database\Relations\Relation;
use Collei\Support\Collections\Collection;

/**
 *	Embodies many-to-many relations
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class OneToMany extends Relation
{
	/**
	 *	Builds and instantiates
	 *
	 *	@param	\Collei\Database\Yanfei\Model	$near
	 *	@param	\Collei\Database\Yanfei\Model	$far
	 *	@param	string	$foreignKey	foreign key in the far table pointing to the local table
	 *	@param	string	$localKey	local table key
	 *	@return	void
	 */
	public function __construct(Model $near, Model $far, string $foreignKey = null, string $localKey = null)
	{
		$this->left = $near;
		$this->right = $far;

		$this->inferKeys($localKey, $foreignKey);
	}

	/**
	 *	Returns the data from the relation results
	 *
	 *	@return	mixed
	 */
	protected function fetchData()
	{
		$foreign = $this->rightKey;
		$local = $this->leftKey;

		$farEntity = $this->right->getTable();

		return DB::from($farEntity)
					->select('*')
					->where()->is($foreign, $this->left->$local)
					->gather();
	}
	
}


