<?php
namespace Collei\Support\Values;

use Iterator;
use Traversable;
use Collei\Contracts\Jsonable;

/**
 *	Encapsulated, read-only data fields.
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-11-10
 */
class Capsule implements Iterator, Jsonable
{
	/**
	 *	@var array $contained
	 */
	protected $contained = [];

	/**
	 *	@var array $names
	 */
	protected $names = [];

	/**
	 *	@var int $position
	 */
	protected $position = 0;

	/**
	 *	Returns the value tied to the name
	 *
	 *	@method	__get
	 *	@param	string	$name
	 *	@return	mixed
	 */
	public function __get(string $name)
	{
		return $this->contained[$name] ?? null;
	}

	/**
	 *	@method	__debugInfo
	 *	@return	array
	 */
	public function __debugInfo()
	{
		return $this->contained;
	}

	/**
	 *	Returns true if the property exists, false otherwise
	 *
	 *	@method	current
	 *	@return	mixed
	 */
	public function current()
	{
		return $this->contained[$this->names[$this->position]];
	}

	/**
	 *	Returns the current index
	 *
	 *	@method	key
	 *	@return	mixed
	 */
	public function key()
	{
		return $this->names[$this->position];
	}

	/**
	 *	Advances the pointer a step forth
	 *
	 *	@method	next
	 *	@return	void
	 */
	public function next()
	{
		++$this->position;
	}

	/**
	 *	Performs pointer rewind
	 *
	 *	@method	rewind
	 *	@return	void
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 *	Returns if the collection exists and it is valid
	 *
	 *	@method	rewind
	 *	@return	bool
	 */
	public function valid()
	{
		return isset($this->contained[$this->names[$this->position]]);
	}

	/**
	 *	returns the object data as array
	 *
	 *	@method	asArray
	 *	@return	array
	 */
	public function asArray()
	{
		return ($array = $this->contained);
	}

	/**
	 *	converts the object data to Json string
	 *
	 *	@method	toJson
	 *	@return	string
	 */
	public function toJson()
	{
		return json_encode($this->contained);
	}

	/**
	 *	Generates a Capsule with the array members as readonly properties
	 *
	 *	@param	array	$data
	 *	@return	\Collei\Support\Values\Capsule
	 */
	public static function from(array $data)
	{
		return new class($data) extends Capsule
		{
			public function __construct(array $data)
			{
				foreach ($data as $k => $v)
				{
					$this->contained[$k] = $v;
					$this->names[] = $k;
				}
			} 
		};
	}

}


