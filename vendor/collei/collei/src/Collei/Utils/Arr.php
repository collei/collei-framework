<?php
namespace Collei\Utils;

use Collei\Utils\Str;
use InvalidArgumentException;
use Closure;

/**
 *	Reunites array helper functions
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
abstract class Arr
{
	/**
	 *	Returns an array with a $thing inserted at $where index
	 *
	 *	@static
	 *	@param	mixed	$thing
	 *	@param	array	$original
	 *	@param	int		$where
	 *	@return	array
	 */
	public static function insert($thing, array $original, int $where)
	{
		$brandNew = [];
		// if to be the first, inserts it first in the new array
		if ($where <= 0) {
			$brandNew[] = $thing;
			foreach ($original as $piece) {
				$brandNew[] = $piece;
			}
			return $brandNew;
		}
		// if to be the last, inserts it as the last in the new array
		if ($where >= count($original)) {
			$brandNew = $original;
			$brandNew[] = $thing;
			return $brandNew;
		}
		// if to be in the middle, creates a couple of arrays...
		$before = [];
		$after = [];
		foreach ($original as $ci => $piece) {
			if ($ci < $priority) {
				$before[] = $piece;
			} else {
				$after[] = $piece;
			}
		}
		//...and joins them
		foreach ($before as $piece) {
			$brandNew[] = $piece;
		}
		$brandNew[] = $thing;
		foreach ($after as $piece) {
			$brandNew[] = $piece;
		}
		//
		return $brandNew;
	}

	/**
	 *	Filters an array by their values
	 *
	 *	@static
	 *	@param	array	$array
	 *	@param	array	$values
	 *	@return	array
	 */
	public static function except(array $array, array $values)
	{
		return array_diff($array, $values);
	}

	/**
	 *	Filters an array by their keys
	 *
	 *	@static
	 *	@param	array	$array
	 *	@param	array	$keys
	 *	@return	array
	 */
	public static function exceptKeys(array $array, array $keys)
	{
		return array_diff_key($array, array_flip($keys));
	}

	/**
	 *	Returns an array with the keys as values, ignoring any of original values
	 *
	 *	@static
	 *	@param	array	$array
	 *	@return	array
	 */
	public static function keys(array $array)
	{
		return array_keys($array);
	}

	/**
	 *	Returns an array with the values only
	 *
	 *	@static
	 *	@param	array	$array
	 *	@return	array
	 */
	public static function values(array $array)
	{
		return array_values($array);
	}

	/**
	 *	Returns an array with the same count of elements of the given array, but
	 *	with the $value as the value of every key
	 *
	 *	@static
	 *	@param	array	$array
	 *	@param	string	$value
	 *	@return	array
	 */
	public static function repeats(array $array, string $value)
	{
		$values = [];
		//
		foreach ($array as $n => $v) {
			$values[$n] = $value;
		}
		//
		return $values;
	}

	/**
	 *	Interlaces associative arrays in a string, with both keys and values
	 *
	 *	@static
	 *	@param	string	$glue
	 *	@param	array	$array
	 *	@param	string	$symbol
	 *	@return	mixed
	 */
	public static function join(string $glue, array $array, string $symbol = null)
	{
		if (is_null($symbol)) {
			return implode($glue, $array);
		}
		//
		return implode($glue, array_map(
			function($n, $v) use ($symbol) {
				return $n . $symbol . $v;
			},
			self::keys($array),
			self::values($array)
		));
	}

	/**
	 *	Joins the array keys only, discarding any values
	 *
	 *	@static
	 *	@param	string	$glue
	 *	@param	array	$array
	 *	@return	string
	 */
	public static function joinKeys(string $glue, array $array)
	{
		return implode($glue, Arr::keys($array));
	}

	/**
	 *	Transform an associative array in a string through a bit more complex way
	 *
	 *	@static
	 *	@param	string	$glue
	 *	@param	array	$array
	 *	@param	mixed	$holder
	 *	@return	string
	 */
	public static function joinKeyHolders(string $glue, array $array, $holder)
	{
		if (is_callable($holder) || ($holder instanceof Closure)) {
			$holders = [];
			//
			foreach ($array as $n => $v) {
				$holders[] = $holder($n);
			}
			//
			return implode($glue, $holders);
		}
		//
		if (!is_string($holder)) {
			throw new InvalidArgumentException('The argument $holder should be a string or a callable.');
		}
		//
		return implode($glue, Arr::repeats($array, $holder));
	}

	/**
	 *	Transform an associative array in a string through a bit more complex way, part II
	 *
	 *	@static
	 *	@param	string	$glue
	 *	@param	array	$array
	 *	@param	mixed	$holder
	 *	@return	string
	 */
	public static function joinKeyValueHolders(
		string $glue, array $array, $holder
	) {
		if (is_callable($holder) || ($holder instanceof Closure)) {
			$holders = [];
			//
			foreach ($array as $n => $v) {
				$holders[] = $holder($n, $v);
			}
			//
			return implode($glue, $holders);
		}
		//
		if (!is_string($holder)) {
			throw new InvalidArgumentException(
				'The argument $holder should be a string or a callable.'
			);
		}
		//
		return implode($glue, Arr::repeats($array, $holder));
	}

	/**
	 *	Join array values in a string with glue collapse
	 *
	 *		$array = ['/food/','/cereals','rice/','fine-grained']
	 *		PHP's implode('/',$array):
	 *				/food///cereals/rice//fine-grained
	 *		joinCollapsed('/',$array):
	 *				/food/cereals/rice/fine-grained
	 *
	 *	@static
	 *	@param	string	$glue
	 *	@param	array	$array
	 *	@return	mixed
	 */
	public static function joinCollapsed(string $glue, array $array)
	{
		$things = [];
		$first = Str::trimSuffix(array_shift($array) ?? '', $glue);
		$last = Str::trimPrefix(array_pop($array) ?? '', $glue);
		//
		if ($first != '' && $first != $glue) {
			$things[] = Str::trimSuffix($first, $glue);
		}
		//
		foreach ($array as $item) {
			$things[] = Str::trimBoth($item, $glue, $glue);
		}
		//
		if ($last != '' && $last != $glue) {
			$things[] = Str::trimPrefix($last, $glue);
		}
		//
		return Arr::join($glue, $things);
	}

	/**
	 *	Join array values in a string with a couple glues, like opening and
	 *	closing HTML tags
	 *
	 *		$array = ['apple','orange','strawberry','grape']
	 *		joinEnclosed('<li>','</li>',$array):
	 *			<li>apple</li><li>orange</li><li>strawberry</li><li>grape</li>
	 *
	 *	@static
	 *	@param	string	$glue
	 *	@param	array	$array
	 *	@return	mixed
	 */
	public static function joinEnclosed(
		string $prefixGlue, string $suffixGlue, array $array
	) {
		return	$prefixGlue
				. Arr::join($prefixGlue . $suffixGlue, $array)
				. $suffixGlue;
	}

	/**
	 *	Scan the values to define their types
	 *
	 *	@static
	 *	@param	array	$line
	 *	@return	array
	 */
	public static function prospectTypes(array $line)
	{
		$types = [];
		//
		foreach ($line as $n => $cell) {
			$types[$n] = gettype($cell);
		}
		//
		return $types;
	}

	/**
	 *	Checks if the array lines are type-consistent
	 *
	 *	@static
	 *	@param	array	$line
	 *	@param	array	$types
	 *	@return	bool
	 */
	public static function isTypeConsistent(array $line, array $types)
	{
		if (count($line) !== count($types)) {
			return false;
		}
		//
		foreach ($types as $n => $type) {
			if (!array_key_exists($n, $line)) {
				return false;
			}
			//
			$cell = $line[$n];
			//
			if (!(gettype($cell) === $type) || is_null($cell)) {
				return false;
			}
		}
		//
		return true;
	}

	/**
	 *	Checks if the given array has such keys
	 *
	 *	@static
	 *	@param	array	$array
	 *	@param	string	...$keys
	 *	@return	bool
	 */
	public static function hasKeys(array $array, string ...$keys)
	{
		foreach ($keys as $key) {
			if (!array_key_exists($key, $array)) {
				return false;
			}
		}
		//
		return true;
	}

	/**
	 *	Checks if the given array has sub-arrays in a table manner
	 *
	 *	@static
	 *	@param	array		$array
	 *	@return	bool
	 */
	public static function hasLines(array $array)
	{
		$has = true;
		$columns = 0;
		$types = [];
		//
		foreach ($array as $line) {
			if (!is_array($line)) {
				return false;
			}
			//
			if ($columns === 0) {
				$columns = count($line);
			} elseif (count($line) !== $columns) {
				return false;
			}
			//
			if (count($types) === 0) {
				$types = self::prospectTypes($line);
			} elseif (!self::isTypeConsistent($line, $types)) {
				return false;
			}
		}
		//
		return true;
	}

	/**
	 *	Returns the first element
	 *
	 *	@static
	 *	@param	array		$array
	 *	@return	mixed
	 */
	public static function first(array $array)
	{
		return array_shift($array);
	}

	/**
	 *	Returns the last element
	 *
	 *	@static
	 *	@param	array		$array
	 *	@return	mixed
	 */
	public static function last(array $array)
	{
		return array_pop($array);
	}

	/**
	 *	Re-key arrays according transformations implemented by the Closure
	 *
	 *	@static
	 *	@param	array		$array
	 *	@param	\Closure	$transform
	 *	@return	array
	 */
	public static function rekey(array $array, Closure $transform)
	{
		if (!is_callable($transform)) {
			return $array;
		}
		//
		$copy = [];
		foreach ($array as $n => $v) {
			$k = $transform($n);
			//
			if (is_string($k) || is_int($k)) {
				$copy[$k] = $v;
			} else {
				throw new InvalidArgumentException(
					"The closure must return an integer or string value."
				);
			}
		}
		//
		return $copy;
	}

	/**
	 *	Sort arrays
	 *
	 *	@static
	 *	@param	array	...$values
	 *	@return	array
	 */
	public static function sorted(...$values)
	{
		sort($values);
		return $values;
	}

	/**
	 *	Filter array elements by key
	 *
	 *	@static
	 *	@param	array	$source
	 *	@param	array	$keys
	 *	@return	array
	 */
	public static function filterByKey(array $source, array $keys)
	{
		$filter = function($k) use ($keys) {
			return in_array($k, $keys);
		};
		//
		return array_filter($source, $filter, ARRAY_FILTER_USE_KEY);
	}

	/**
	 *	Filter array elements by value
	 *
	 *	@static
	 *	@param	array	$source
	 *	@param	array	$value
	 *	@return	array
	 */
	public static function filterByValue(array $source, array $values)
	{
		$filter = function($v) use ($values) {
			return in_array($v, $values);
		};
		//
		return array_filter($source, $filter);
	}

	/**
	 *	Filter array elements by key and value
	 *
	 *	@static
	 *	@param	array	$source
	 *	@param	Closure	$filter
	 *	@return	array
	 */
	public static function filterByCustom(array $source, Closure $filter)
	{
		return array_filter($source, $filter, ARRAY_FILTER_USE_BOTH);
	}

	/**
	 *	Create an associative array with the given strings as keys
	 *	in the same order you give them 
	 *
	 *	@static
	 *	@param	string	...$keys
	 *	@return	array
	 */
	public static function create(string ...$keys)
	{
		if (empty($keys)) {
			return [];
		}
		//
		$result = [];
		foreach ($keys as $key) {
			$result[$key] = '';
		}
		//
		return $result;
	}

	/**
	 *	Gets an array of objects OR arrays and returns a specific field.
	 *	Returns false if $column does not exist OR it is unreachable
	 *	(e.g., an object private property)
	 *
	 *	@param	array	$items
	 *	@param	string	$column
	 *	@return	array|false
	 */
	public static function column(array $items, string $column)
	{
		$result = [];
		//
		foreach ($items as $item) {
			if (is_array($item) && isset($item[$column])) {
				$result[] = $item[$column];
			} elseif (is_object($item) && isset($item->$column)) {
				$result[] = $item->$column;
			}
		}
		//
		if (empty($result)) {
			return false;
		}
		//
		return $result;
	}


	public static function toDescription(array $assoc)
	{
		
	}

}


