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
use Collei\Utils\Collections\TypedCollection;
use Collei\Utils\Arr;
use Collei\Utils\Str;

/**
 *	Encapsulates a set of related models in a single object instance
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-04-26
 */
class ModelSet
{
	private $models = [];

	private static function nameFrom($model)
	{
		$classname = $model;

		if (is_object($model))
		{
			$classname = get_class($model);
		}

		if ($pos = strrpos($classname, '\\'))
		{
			$classname = substr($classname, $pos + 1);
		}

		return $classname;
	}


	public function __construct()
	{

	}

	public static function with(Model ...$instances)
	{
		$that = new static();

		foreach ($instances as $instance)
		{
			$that->models[static::nameFrom($instance)] = $instance;
		}

		return $that;
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->models))
		{
			return $this->models[$name];
		}

		return null;
	}

}


