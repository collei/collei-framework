<?php
namespace Collei\Database\Meta;

use Collei\Database\Meta\Field;
use Collei\Database\Meta\Table;
use Collei\Support\Values\Value;
use Collei\Support\Calendar\Date;

/**
 *	Embodies database primary key metadata
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class PrimaryKey extends Field
{
	/**
	 *	@var bool
	 */
	private $auto_numerated = null;

	/**
	 *	@var int
	 */
	private $counter = 0;

	/**
	 *	Initializes a new instance of primary key metadata keeper
	 *
	 *	@param	\Collei\Database\Meta\Table	$table
	 *	@param	string	$name
	 *	@param	int		$type
	 *	@param	int		$size
	 */
	public function __construct(Table $table, string $name, int $type = null, int $size = null)
	{
		parent::__construct($table, $name, $type, $size);
		//
		$this->required();
		$this->unique();
		$this->defaultValue(0);
	}

	/**
	 *	Sets this field as autonumerated
	 *
	 *	@return	\Collei\Database\Meta\PrimaryKey
	 */
	public function autoNumerated()
	{
		if (is_null($this->auto_numerated))
		{
			$this->auto_numerated = true;
		}
		return $this;
	}

}


