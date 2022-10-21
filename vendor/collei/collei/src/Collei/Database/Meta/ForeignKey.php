<?php
namespace Collei\Database\Meta;

use Collei\Database\Meta\DS;
use Collei\Database\Meta\Field;
use Collei\Database\Meta\Table;
use Collei\Support\Values\Value;
use Collei\Support\Calendar\Date;
use Collei\Database\DatabaseException;

/**
 *	Embodies database foreign key metadata
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-06-xx
 */
class ForeignKey extends Field
{
	/**
	 *	@var string
	 */
	private $reference_table = null;

	/**
	 *	@var string
	 */
	private $reference_field = null;

	/**
	 *	Resolve links between mutually related tables
	 *
	 *	@return void
	 */
	private function resolveBonds()
	{
		if (is_null($this->reference_table))
		{
			throw new DatabaseException('No foreign table (' .$this->reference_table. ') specified for the foreign key ' . $this->name . ' on ' . $this->table->name);
		}

		$database_name = $this->table->getDatabase()->getName();

		$table = DS::getTable($this->reference_table, $database_name);
		if (is_null($table))
		{
			throw new DatabaseException('No table ' . $this->reference_table . ' was found for the foreign key ' . $this->name . ' on ' . $this->table->name . ' ');
		}

		$this->reference_table = $table;
	}

	/**
	 *	Initializes a new instance of foreign key metadata keeper
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
		$this->defaultValue(0);
	}

	/**
	 *	@var string $foreignTable
	 *	@var string $foreignTableKey
	 */
	public function __get($name)
	{
		if ($name == 'foreignTable')
		{
			return $this->reference_table;
		}
		if ($name == 'foreignTableKey')
		{
			return $this->reference_field;
		}
		return parent::__get($name);
	}

	/**
	 *	Sets the name of the primary key of the foreign table
	 *
	 *	@param	string	$foreignPrimary
	 *	@return	\Collei\Database\Meta\ForeignKey
	 */
	public function references(string $foreignPrimary)
	{
		if (!$this->realized())
		{
			$this->reference_field = $foreignPrimary;
		}
		return $this;
	}

	/**
	 *	Sets the name of the related foreign table
	 *
	 *	@param	string	$foreignTable
	 *	@return	\Collei\Database\Meta\ForeignKey
	 */
	public function on(string $foreignTable)
	{
		if (!$this->realized())
		{
			$this->reference_table = $foreignTable;
		}
		return $this;
	}

	/**
	 *	Mark this field as unchangeable
	 *
	 *	@return	void
	 */
	public function realize()
	{
		$this->resolveBonds();
		parent::realize();
	}

}


