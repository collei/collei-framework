<?php
namespace Collei\Database\Meta;

use Collei\Database\Box\Databox;
use Collei\Database\DatabaseException;
use Collei\Database\Meta\Field;
use Collei\Database\Meta\Table;
use Collei\Support\Values\Value;
use Collei\Support\Calendar\Date;

/**
 *	Embodies a preliminary, not yet confirmed foreign key
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-08-xx
 */
class UnresolvedForeignKey extends Field
{
	/**
	 *	@var \Collei\Database\Meta\Table $reference_table
	 */
	private $reference_table = null;

	/**
	 *	@var string $reference_table
	 */
	private $reference_field = null;

	/**
	 *	'Confirms' the foreign key
	 *
	 *	@return	void
	 */
	private function resolveBonds()
	{
		if (is_null($this->reference_table)) {
			throw new DatabaseException(
				'No foreign table specified for the foreign key '
					. $this->name . ' on '
					. $this->table->name
			);
		}
		//
		$table = Databox::getTable($this->reference_table);
		if (is_null($table)) {
			throw new DatabaseException(
				'No table ' . $this->reference_table
					. ' was found for the foreign key ' . $this->name
					. ' on ' . $this->table->name . ' '
			);
		}
		//
		$this->reference_table = $table;
	}

	/**
	 *	Builds and initializes a new instance
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
	 *	@property \Collei\Database\Meta\Table $foreignTable
	 *	@property string $foreignTableKey
	 */
	public function __get($name)
	{
		if ($name == 'foreignTable') {
			return $this->reference_table;
		}
		//
		if ($name == 'foreignTableKey') {
			return $this->reference_field;
		}
		//
		return parent::__get($name);
	}

	/**
	 *	Register the primary key of the another table
	 *
	 *	@param	string	$foreignPrimary
	 *	@return	\Collei\Database\Meta\UnresolvedForeignKey
	 */
	public function references(string $foreignPrimary)
	{
		if (!$this->realized()) {
			$this->reference_field = $foreignPrimary;
		}
		//
		return $this;
	}

	/**
	 *	Register the another table that keeps the primary key
	 *
	 *	@param	string	$foreignTable
	 *	@return	\Collei\Database\Meta\UnresolvedForeignKey
	 */
	public function on(string $foreignTable)
	{
		if (!$this->realized()) {
			$this->reference_table = $foreignTable;
		}
		//
		return $this;
	}

	/**
	 *	Resolves the link between the tables
	 *
	 *	@return	void
	 */
	public function extractForeignField()
	{
		$this->resolveBonds();
		parent::realize();
	}

}


