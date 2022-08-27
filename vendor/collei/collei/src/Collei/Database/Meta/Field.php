<?php
namespace Collei\Database\Meta;

use Collei\Database\Meta\Table;
use Collei\Utils\Values\Value;
use Collei\Utils\Calendar\Date;
use Collei\Pacts\Realizable;

/**
 *	Embodies database field metadata
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class Field implements Realizable
{
	/**
	 *	@var \Collei\Utils\Values\Value
	 */
	private $table = null;

	/**
	 *	@var string
	 */
	private $name = null;

	/**
	 *	@var int
	 */
	private $type = null;

	/**
	 *	@var int
	 */
	private $size = null;

	/**
	 *	@var bool
	 */
	private $is_unique = false;

	/**
	 *	@var bool
	 */
	private $is_nullable = true;

	/**
	 *	@var mixed
	 */
	private $default_value = null;

	/**
	 *	@var bool
	 */
	private $realized = false;

	/**
	 *	Builds a new instance of the table metadata container
	 *
	 *	@param	\Collei\Database\Meta\Table	
	 *	@param	string	$name
	 *	@param	int		$type
	 *	@param	int		$size
	 */
	public function __construct(Table $table, string $name, int $type = null, int $size = null)
	{
		$this->table = $table;
		$this->name = $name;
		if (!is_null($type))
		{
			$this->type($type);
		}
		if (!is_null($size))
		{
			$this->size($size);
		}
	}

	/**
	 *	@var \Collei\Database\Meta\Table $table
	 *	@var string	$name
	 *	@var int $type
	 *	@var int $size
	 *	@var bool $isUnique
	 *	@var bool $isNullable
	 *	@var mixed $defaultValue
	 */
	public function __get($name)
	{
		if (in_array($name, ['table','name','type','size']))
		{
			return $this->$name;
		}
		if ($name == 'isUnique')
		{
			return $this->is_unique;
		}
		if ($name == 'isNullable')
		{
			return $this->is_nullable;
		}
		if ($name == 'defaultValue')
		{
			return $this->default_value;
		}
		trigger_error('There is no such field: ' . $name . '.', E_USER_ERROR);
	}

	/**
	 *	Sets the type of the field
	 *
	 *	@param	int|string	$type
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function type($type)
	{
		if (!$this->realized())
		{
			if (is_int($type))
			{
				$this->type = $type;
			}
			else
			{
				$this->type = Value::typeFromString($type);
			}
		}
		return $this;
	}

	/**
	 *	Sets the size of the field
	 *
	 *	@param	int	$type
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function size(int $size)
	{
		if (!$this->realized())
		{
			if ($size < 0)
			{
				$size *= -1;
			}
			//
			$this->size = $size;
		}
		return $this;
	}

	/**
	 *	Sets the field as unique
	 *
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function unique()
	{
		if (!$this->realized())
		{
			$this->is_unique = true;
		}
		return $this;
	}

	/**
	 *	Sets the field as nullable
	 *
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function nullable()
	{
		if (!$this->realized())
		{
			$this->is_nullable = true;
		}
		return $this;
	}

	/**
	 *	Sets the field as not nullable
	 *
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function required()
	{
		if (!$this->realized())
		{
			$this->is_nullable = false;
		}
		return $this;
	}

	/**
	 *	Sets the default value of the field
	 *
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function defaultValue($value)
	{
		if (!$this->realized())
		{
			$this->default_value = $value;
		}
		return $this;
	}

	/**
	 *	Sets the default value of the field
	 *
	 *	@param	mixed	$value
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function default($value)
	{
		return $this->defaultValue($value);
	}

	/**
	 *	Sets the default timestamp for date/time fields
	 *
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function useCurrent()
	{
		return $this->defaultValue('current_timestamp()');
	}

	/**
	 *	Make the applied changes unchangeable henceforth
	 *
	 *	@return	void
	 */
	public function realize()
	{
		$this->nullable();
		$this->realized = true;
	}

	/**
	 *	Returns if the field changes has been made unchangeable
	 *
	 *	@return	bool
	 */
	public function realized()
	{
		return $this->realized;
	}

}


