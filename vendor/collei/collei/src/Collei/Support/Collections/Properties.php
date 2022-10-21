<?php 
namespace Collei\Support\Collections;

use Iterator;

/**
 *	Embodies the treatment of lists of name-value pairs
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class Properties implements Iterator
{
	/**
	 *	@var array
	 */
	private $property_list = [];

	/**
	 *	Initializes a new instance
	 *
	 *	@param	array	$values	array with string indexes
	 */
	public function __construct(array $values = [])
	{
		$this->property_list = $values;
	}

	/**
	 *	Inserts a new name-value
	 *
	 *	@param	string	$name
	 *	@param	mixed	$value
	 *	@return	void
	 */
	public function add(string $name, $value)
	{
		if (!$this->has($name)) {
			$this->property_list[$name] = $value;
		}
	}

	/**
	 *	Inserts new name-value pairs
	 *
	 *	@param	array	$values	array with string indexes
	 *	@return	void
	 */
	public function adds(array $values, bool $overwriteExisting = false)
	{
		if ($overwriteExisting) {
			foreach ($values as $n => $v) {
				$this->set($n, $v);
			}
		} else {
			foreach ($values as $n => $v) {
				$this->add($n, $v);
			}
		}
	}

	/**
	 *	Removes a value
	 *
	 *	@param	string	$name
	 *	@return	void
	 */
	public function remove(string $name)
	{
		if ($this->has($name)) {
			unset($this->property_list[$name]);
		}
	}

	/**
	 *	Removes all values
	 *
	 *	@return	void
	 */
	public function clear()
	{
		foreach ($this->property_list as $n => $v) {
			unset($this->property_list[$n]);
		}
		//
		$this->property_list = [];
	}

	/**
	 *	Returns all value keys
	 *
	 *	@return	array
	 */
	public function names()
	{
		return array_keys($this->property_list);
	}

	/**
	 *	Ask for key existence
	 *
	 *	@return	bool
	 */
	public function has(string $name)
	{
		return array_key_exists($name, $this->property_list);
	}

	/**
	 *	Returns the value tied to the specified name
	 *
	 *	@param	string	$name
	 *	@param	mixed	$default
	 *	@return	mixed
	 */
	public function get(string $name, $default = null)
	{
		if ($this->has($name)) {
			if (!empty($this->property_list[$name])) {
				return $this->property_list[$name];
			}
		}
		return $default;
	}

	/**
	 *	Sets a value to a name
	 *
	 *	@param	string	$name
	 *	@param	mixed	$value
	 *	@return	void
	 */
	public function set(string $name, $value)
	{
		$this->property_list[$name] = $value;
	}

	/**
	 *	Returns an associative array with all name-value pairs
	 *
	 *	@return	array
	 */
	public function asArray()
	{
		$arr = [];
		//
		foreach ($this->property_list as $n => $v) {
			$arr[$n] = $v;
		}
		//
		return $arr;
	}

	/**
	 *	Returns an array with all set names
	 *
	 *	@return	array
	 */
	public function asArrayOfNames()
	{
		$arr = [];
		//
		foreach ($this->property_list as $n => $v) {
			$arr[] = $n;
		}
		//
		return $arr;
	}

	/**
	 *	Returns the current element value
	 *	origin: \Iterator
	 *
	 *	@return	mixed 
	 */
	public function current()
	{
		return current($this->property_list);
	}

	/**
	 *	Returns the current element key
	 *	origin: \Iterator
	 *
	 *	@return	mixed 
	 */
	public function key()
	{
		return key($this->property_list);
	}

	/**
	 *	Moves the cursor to the next element 
	 *	origin: \Iterator
	 *
	 *	@return	void
	 */
	public function next()
	{
		next($this->property_list);
	}

	/**
	 *	Resets the cursor position to the first element 
	 *	origin: \Iterator
	 *
	 *	@return	void
	 */
	public function rewind()
	{
		reset($this->property_list);
	}

	/**
	 *	Returns if the current cursor position is valid 
	 *	origin: \Iterator
	 *
	 *	@return	bool
	 */
	public function valid()
	{
		return key($this->property_list) !== null;
	}


}


