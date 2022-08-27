<?php
namespace ColleiLang;

/**
 *	Embodies a user-defined enumerable and their abilities
 *
 *	@author Collei Inc. <collei@collei.com.br>
 *	@author Alarido <alarido.su@gmail.com>
 *	@since 2022-08-08
 */
abstract class ColleiEnum
{
	/**
	 *	@const array ALLOWED
	 */
	public const ALLOWED = [];

	/**
	 *	@var string $name
	 */
	protected $name = '';

	/**
	 *	Builds a named instance
	 *
	 *	@param	string	$name
	 */
	private function __construct(string $name)
	{
		$this->name = $name;
	}

	/**
	 *	Converts itself into string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		return $this->name;
	}

	/**
	 *	Returns the name name 
	 *
	 *	@return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *	Returns if both names match 
	 *
	 *	@param	static|string	$name
	 *	@return	bool
	 */
	public function is($name)
	{
		if (\is_string($name)) {
			return $name === $this->name;
		} elseif ($name instanceof static) {
			return $name->name === $this->name;
		}
		//
		return false;
	}

	/**
	 *	Returns if the contained name is one of the list
	 *
	 *	@param	static|string	...$names
	 *	@return	bool
	 */
	public function in(...$names)
	{
		$result = false;
		//
		foreach ($names as $name) {
			$result = $result || $this->is($name);
		}
		//
		return $result;
	}

	/**
	 *	Returns an instance based upon the string name
	 *
	 *	@return	string
	 */
	public static function new(string $name)
	{
		$name = \ucfirst(\strtolower(\trim($name)));
		//
		if (\in_array($name, static::ALLOWED)) {
			return new static($name);
		}
		//
		return null;
	}

	/**
	 *	Returns an array of instances of names 
	 *
	 *	@return	string
	 */
	public static function asArray()
	{
		$names = [];
		//
		foreach (static::ALLOWED as $name) {
			$names[] = new static($name);
		}
		//
		return $names;
	}

}

