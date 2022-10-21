<?php
namespace Collei\Database\Yanfei;

use InvalidArgumentException;
use Closure;
use Collei\Database\DatabaseException;
use Collei\Database\Meta\DS;
use Collei\Database\Meta\Table;
use Collei\Database\Yanfei\Model;
use Collei\Database\Query\DB;
use Collei\Database\Query\Select;
use Collei\Database\Query\Clauses\Where;
use Collei\Support\Collections\TypedCollection;
use Collei\Support\Arr;
use Collei\Support\Str;

/**
 *	Encapsulates a collection of Models of a given type
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class ModelResult extends TypedCollection
{
	/**
	 *	Builds and instantiates a Collection
	 *
	 *	@param	string	$modelClass
	 */
	public function __construct(string $modelClass = null)
	{
		if (is_null($modelClass)) {
			parent::__construct(Model::class);
		} else {
			if (
				is_a($modelClass, Model::class, true)
				|| ($modelClass === Model::class)
			) {
				parent::__construct($modelClass);
			} else {
				throw new InvalidArgumentException(
					'Class passed to ModelResult::__construct() is not '
					. Model::class
					. ' nor a sublclass of it.'
				);
			}
		}
	}

	/**
	 *	Filters a Collection extracting another Collection that may be of another type
	 *
	 *	@param	\Closure	$each
	 *	@param	string		$modelClass
	 *	@return	\Collei\Database\Yanfei\ModelResult
	 */
	public function filter(Closure $each, string $modelClass = null)
	{
		$filteredChain = [];
		//
		foreach ($this as $piece) {
			$thing = $each($piece);
			if ($thing !== false) {
				$filteredChain[] = $thing;
			}
		}
		//
		$modelClass = $modelClass ?? Model::class;
		//
		return ModelResult::fromTypedArray($filteredChain, $modelClass, false);
	}

	/**
	 *	Filters a Collection upon field name, extracting data and returning it as array
	 *
	 *	@param	string	$fieldName
	 *	@return	array
	 */
	public function filterData(string $fieldName, Closure $condition = null)
	{
		$filteredData = [];
		$data = '';
		//
		if (!is_null($condition)) {
			foreach ($this as $piece) {
				if (isset($piece->$fieldName) && $condition($piece)) {
					$filteredData[] = $piece->$fieldName;
				}
			}
		} else {
			foreach ($this as $piece) {
				if (isset($piece->$fieldName)) {
					$filteredData[] = $piece->$fieldName;
				}
			}
		}
		//
		return $filteredData;
	}

	/**
	 *	A convenient mode for using with Model::from([field => 'value']) queries
	 *	that may return either Model or ModelResult instances.
	 *
	 *	e.g.: get the first person of list
	 *
	 *	$workers = Employee::from(['city' => 'New York']);
	 *	$first = $workers->firstResult();
	 *
	 *	@return	instanceof Model
	 */
	public function firstResult()
	{
		return $this->first();
	}

	/**
	 *	Returns the contents as JSON
	 *
	 *	@return	string
	 */
	public function toJson()
	{
		$array = [];
		//
		foreach ($this as $item) {
			$array[] = json_decode($item->asJson());
		}
		//
		return json_encode($array);
	}

}


