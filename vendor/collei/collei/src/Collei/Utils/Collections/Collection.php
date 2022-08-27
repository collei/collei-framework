<?php
namespace Collei\Utils\Collections;

use Iterator;
use Traversable;

/**
 *	Embodies the treatment of lists of name-value pairs
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class Collection implements Iterator
{
	/**
	 *	@var array $data
	 */
	protected $data = [];

	/**
	 *	@var mixed $position
	 */
	private $position;

	/**
	 *	Instantiates a new collection
	 *
	 */
	public function __construct()
	{
		$this->position = 0;
	}

	/**
	 *	Adds an element
	 *
	 *	@param	mixed	$element
	 *	@return	void
	 */
	public function add($element)
	{
		$this->data[] = $element;
	}

	/**
	 *	Returns the current value
	 *
	 *	@return	mixed
	 */
	public function current()
	{
		return $this->data[$this->position];
	}

	/**
	 *	Returns the current index
	 *
	 *	@return	mixed
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 *	Advances the pointer a step forth
	 *
	 *	@return	void
	 */
	public function next()
	{
		++$this->position;
	}

	/**
	 *	Performs pointer rewind
	 *
	 *	@return	void
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 *	Returns if the collection exists and it is valid
	 *
	 *	@return	bool
	 */
	public function valid()
	{
		return isset($this->data[$this->position]);
	}

	/**
	 *	Returns the collection size
	 *
	 *	@return	int
	 */
	public function size()
	{
		return count($this->data);
	}

	/**
	 *	Returns if the collection is empty
	 *
	 *	@return	bool
	 */
	public function empty()
	{
		return count($this->data) === 0;
	}

	/**
	 *	Returns the first element of the collection, if it exists
	 *
	 *	@return	mixed
	 */
	public function first()
	{
		return $this->data[0] ?? null;
	}

	/**
	 *	Returns the last element of the collection, if it exists
	 *
	 *	@return	mixed
	 */
	public function last()
	{
		return $this->data[$this->size() - 1] ?? null;
	}

	/**
	 *	Returns the previous element of the collection, if it exists
	 *
	 *	@return	mixed
	 */
	public function previous()
	{
		$previousPosition = $this->position - 1;

		if ($previousPosition >= 0)
		{
			return $this->data[$previousPosition] ?? null;
		}

		return null;
	}

	/**
	 *	Returns the $nth element of the collection, if it exists
	 *
	 *	@param	int		$nth
	 *	@return	mixed
	 */
	public function nth(int $nth)
	{
		return $this->data[$nth] ?? null;
	}

	/**
	 *	Builds a generic collection containing the array elements
	 *
	 *	@static
	 *	@param	array	$array
	 *	@return	\Collei\Utils\Collections\Collection
	 */
	public static function fromArray(array $array = null)
	{
		$collection = new static();

		if (!is_null($array))
		{
			foreach ($array as $n => $v) {
				$collection->data[] = $v;
			}
		}

		return $collection;
	}

	/**
	 *	Builds an empty collection
	 *
	 *	@static
	 *	@return	\Collei\Utils\Collections\Collection
	 */
	public static function fromEmpty()
	{
		return new static();
	}

}


