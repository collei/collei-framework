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
 *	Encapsulates a set of related models in a single object instance
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-04-26
 */
class ModelSet
{
	/**
	 *	@var array $models
	 */
	private $models = [];

	/**
	 *	Obtains the name of the given $model instance
	 *
	 *	@param	mixed	$model
	 *	@return	string
	 */
	private static function nameFrom($model)
	{
		$className = $model;
		//
		if (is_object($model)) {
			$className = get_class($model);
		}
		//
		if ($pos = strrpos($className, '\\')) {
			$className = substr($className, $pos + 1);
		}
		//
		return $className;
	}

	/**
	 *	Initializes a new instance...
	 *
	 *	@return	void
	 */
	public function __construct()
	{
	}

	/**
	 *	Appends model instances to a brand new set.
	 *
	 *	@param	\Collei\Database\Yanfei\Model	...$instances
	 *	@return	\Collei\Database\Yanfei\ModelSet
	 */
	public static function with(Model ...$instances)
	{
		$that = new static();
		//
		foreach ($instances as $instance) {
			$that->models[static::nameFrom($instance)] = $instance;
		}
		//
		return $that;
	}

	/**
	 *	Returns the corresponding instance of the $name model
	 *
	 *	@param	\Collei\Database\Yanfei\Model	...$instances
	 *	@return	\Collei\Database\Yanfei\ModelSet
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->models)) {
			return $this->models[$name];
		}
		//
		return null;
	}

}


