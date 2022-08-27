<?php
namespace Collei\Utils\Collections;

use Iterator;
use Traversable;
use InvalidArgumentException;

/**
 *	Embodies the treatment of lists of name-value pairs
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-08-xx
 */
class TypedCollection extends Collection implements Iterator
{
	/**
	 *	@var string $enforcedType
	 */
	private $enforcedType;

	/**
	 *	Instantiates a new collection instance of the given type
	 *
	 *	@param	string	$enforcedType
	 */
	public function __construct(string $enforcedType)
	{
		parent::__construct();

		$this->enforcedType = $enforcedType;
	}

	/**
	 *	Returns the type of the collection elements
	 *
	 *	@return	string
	 */
	public function getType()
	{
		return $this->enforcedType;
	}

	/**
	 *	Adds an element
	 *
	 *	@param	mixed	$element
	 *	@return	void
	 */
	public function add($element)
	{
		if (!is_a($element, $this->enforcedType))
		{
			throw new InvalidArgumentException('Argument is not an instance of ' . $this->enforcedType);
		}

		$this->data[] = $element;
	}

	/**
	 *	Returns if this collection is of the given type
	 *
	 *	@param	string	$type
	 *	@return	bool
	 */
	public function isOfType(string $type)
	{
		return $this->enforcedType === $type;
	}

	/**
	 *	Creates a new instance from an array
	 *
	 *	@static
	 *	@param	array	$array
	 *	@param	string	$enforcedType
	 *	@param	bool	$ignoreError
	 *	@return	instanceof \Collei\Utils\Collections\TypedCollection
	 */
	public static function fromTypedArray(array $array = null, string $enforcedType, bool $ignoreError = true)
	{
		$collection = new static($enforcedType);

		if (!is_null($array))
		{
			foreach ($array as $n => $v)
			{
				if (is_object($v))
				{
					if (get_class($v) === $enforcedType || is_subclass_of($v, $enforcedType) || in_array($enforcedType, class_implements($v)))
					{
						$collection->data[] = $v;
					}
					elseif ($enforcedType === 'object')
					{
						$collection->data[] = $v;
					}
					elseif (!$ignoreError)
					{
						throw new InvalidArgumentException('Array passed to TypedCollection::fromArray has values of other types than ' . $enforcedType);
					}
				}
				elseif (gettype($v) === $enforcedType)
				{
					$collection->data[] = $v;
				}
				elseif (!$ignoreError)
				{
					throw new InvalidArgumentException('Array passed to TypedCollection::fromArray has values of other types than ' . $enforcedType);
				}
			}
		}

		return $collection;
	}

}

