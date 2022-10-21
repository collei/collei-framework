<?php
namespace Collei\Support\Values;

/**
 *	Embodies tasks on value
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-11-10
 */
class Ball
{
	/**
	 *	@var mixed $contained
	 */
	private $contained;

	/**
	 *	Instantiates a Ball value instance with $anything contained inside
	 *
	 *	@param	mixed	$anything
	 */
	public function __construct($anything = null)
	{
		$this->set($anything);
	}

	/**
	 *	Calls any method of the underlying contained object (if it is an object instance)
	 *
	 *	@param	mixed	$name
	 *	@param	array	$args
	 *	@return	mixed
	 */
	public function __call($name, $args)
	{
		if (function_exists($name))
		{
			return $name(...$args);
		}
		if (is_object($this->contained))
		{
			if (method_exists($this->contained, $name))
			{
				return $this->contained->$name(...$args);
			}
		}
	}

	/**
	 *	Returns any property in the underlying contained object (if it is an object instance)
	 *
	 *	@param	mixed	$name
	 *	@return	mixed
	 */
	public function __get($name)
	{
		if (is_object($this->contained))
		{
			if (isset($this->contained->$name))
			{
				return $this->contained->$name;
			}
		}
	}

	/**
	 *	Sets any property in the underlying contained object (if it is an object instance)
	 *
	 *	@param	mixed	$name
	 *	@param	mixed	$value
	 *	@return	void
	 */
	public function __set($name, $value)
	{
		if (is_object($this->contained))
		{
			if (isset($this->contained->$name))
			{
				$this->contained->$name = $value;
			}
		}
	}

	/**
	 *	Returns the contained value as it is
	 *
	 *	@return	mixed
	 */
	public function get()
	{
		return $this->contained;
	}

	/**
	 *	Sets the contained value
	 *
	 *	@param	mixed	$anything
	 *	@return	void
	 */
	public function set($anything)
	{
		$this->contained = $anything;
	}

	/**
	 *	Returns if has any contained, not-null value
	 *
	 *	@return	bool
	 */
	public function has()
	{
		!is_null($this->contained);
	}

	/**
	 *	Returns the PHP type of the contained value
	 *
	 *	@return	string
	 */
	public function type()
	{
		return gettype($this->contained);
	}

	/**
	 *	Returns whether the contained value is instance of the given $class
	 *
	 *	@param	string	$class
	 *	@return	bool
	 */
	public function instanceOf(string $class)
	{
		if (!$this->has())
		{
			return false;
		}

		if (!is_object($this->contained))
		{
			return false;
		}

		return is_subclass_of($this->contained, $class) || in_array($class, class_implements($this->contained));
	}

}


