<?php
namespace Collei\Database\Meta;

use Collei\Database\Meta\Database;
use Collei\Database\Meta\PrimaryKey;
use Collei\Database\Meta\ForeignKey;
use Collei\Database\Meta\UnresolvedForeignKey;
use Collei\Database\Meta\Field;
use Collei\Support\Values\Value;

/**
 *	Encapsulates table metadata
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-07-xx
 */
class Table
{
	/**
	 *	@var \Collei\Database\Meta\Database $database
	 */
	private $database = null;

	/**
	 *	@var string $name
	 */
	private $name = '';

	/**
	 *	@var \Collei\Database\Meta\PrimaryKey $primaryKey
	 */
	private $primaryKey = '';

	/**
	 *	@var array $fields
	 */
	private $fields = [];

	/**
	 *	@var array $foreign_keys
	 */
	private $foreign_keys = [];

	/**
	 *	@var array $foreign_keys_unresolved
	 */
	private $foreign_keys_unresolved = [];

	/**
	 *	Creates a new Table instance
	 *
	 *	@param	string	$name
	 *	@param	\Collei\Database\Meta\Database	$database
	 */
	public function __construct(string $name, Database $database)
	{
		$this->name = $name;
		$this->database = $database;
	}

	/**
	 *	Returns the name of the table
	 *
	 *	@return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *	Returns the underlying database in which the table is contained
	 *
	 *	@return	\Collei\Database\Meta\Database
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 *	Returns the primary key
	 *
	 *	@return	\Collei\Database\Meta\PrimaryKey
	 */
	public function getKey()
	{
		return $this->primaryKey;
	}

	/**
	 *	Returns if the specified field exists
	 *
	 *	@param	string	$fieldName
	 *	@return	bool
	 */
	public function hasField(string $fieldName)
	{
		return (!is_null($this->primaryKey) && ($this->primaryKey->name == $fieldName))
				|| array_key_exists($fieldName, $this->fields);
	}

	/**
	 *	Creates a primary key of type integer
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\PrimaryKey
	 */
	public function increments(string $name)
	{
		$primaryKey = (new PrimaryKey($this, $name, Value::TYPE_INT, 4))->autoNumerated();
		$this->primaryKey = $primaryKey;
		return $primaryKey;
	}

	/**
	 *	Creates a primary key of type bigint
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\PrimaryKey
	 */
	public function bigIncrements(string $name)
	{
		$primaryKey = (new PrimaryKey($this, $name, Value::TYPE_INT, 8))->autoNumerated();
		$this->primaryKey = $primaryKey;
		return $primaryKey;
	}

	/**
	 *	Adds a new foreign key
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\UnresolvedForeignKey|\Collei\Database\Meta\ForeignKey
	 */
	public function foreign(string $name)
	{
		foreach ($this->fields as $n => $field)
		{
			if ($n == $name)
			{
				$foreign_key = new ForeignKey($this, $name, $field->type, $field->size);
				$this->foreign_keys[$name] = $foreign_key;
				return $foreign_key;
			}
		}
		//
		$unresolved_foreign_key = new UnresolvedForeignKey($this, $name, Value::TYPE_UNTYPED, 0);
		$this->foreign_keys_unresolved[$name] = $unresolved_foreign_key;
		return $unresolved_foreign_key;
	}

	/**
	 *	Alias of boolean(). Creates a new field of type boolean
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function bool(string $name)
	{
		return $this->boolean($name);
	}

	/**
	 *	Creates a new field of type boolean
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function boolean(string $name)
	{
		$field = new Field($this, $name, Value::TYPE_BOOL, 1);
		$this->fields[$name] = $field;
		return $field;
	}

	/**
	 *	Alias of integer(). Creates a new field of type integer
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function int(string $name)
	{
		return $this->integer($name);
	}

	/**
	 *	Creates a new field of type integer
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function integer(string $name)
	{
		$field = new Field($this, $name, Value::TYPE_INT, 4);
		$this->fields[$name] = $field;
		return $field;
	}

	/**
	 *	Alias of bigInteger(). Creates a new field of type bigint
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function bigint(string $name)
	{
		return $this->bigInteger($name);
	}

	/**
	 *	Creates a new field of type bigint
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function bigInteger(string $name)
	{
		$field = new Field($this, $name, Value::TYPE_INT, 8);
		$this->fields[$name] = $field;
		return $field;
	}

	/**
	 *	Creates a new field of type float
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function float(string $name)
	{
		$field = new Field($this, $name, Value::TYPE_DOUBLE, 4);
		$this->fields[$name] = $field;
		return $field;
	}

	/**
	 *	Creates a new field of type double
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function double(string $name)
	{
		$field = new Field($this, $name, Value::TYPE_DOUBLE, 8);
		$this->fields[$name] = $field;
		return $field;
	}

	/**
	 *	Creates a new field of type string
	 *
	 *	@param	string	$name
	 *	@param	int		$size
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function string(string $name, int $size = 50)
	{
		$field = new Field($this, $name, Value::TYPE_STRING, $size);
		$this->fields[$name] = $field;
		return $field;
	}

	/**
	 *	Creates a new field of type date/time
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function timestamp(string $name)
	{
		$field = new Field($this, $name, Value::TYPE_DATE, 4);
		$this->fields[$name] = $field;
		return $field;
	}

	/**
	 *	Alias of timestamp(). Creates a new field of type date/time
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Database\Meta\Field
	 */
	public function datetime(string $name)
	{
		return $this->timestamp($name);
	}

	/**
	 *	Adds timestamp control fields
	 *
	 *	@return	void
	 */
	public function timestamps()
	{
		$this->timestamp('created_at')->useCurrent();
		$this->timestamp('updated_at')->useCurrent();
	}

	/**
	 *	Make all children objects define and consolidate their metadata about foreign keys 
	 *
	 *	@return	void
	 */
	private function resolveUnresolvedForeignKeys()
	{
		foreach ($this->foreign_keys_unresolved as $name => $uf_key)
		{
			foreach ($this->fields as $n => $field)
			{
				if ($n == $name)
				{
					$foreign_key = new ForeignKey($this, $name, $field->type, $field->size);
					$this->foreign_keys[$name] = $foreign_key;
				}
			}
		}
		$this->foreign_keys_unresolved = null;
	}

	/**
	 *	Create a primary key if no primary key was created before
	 *
	 *	@return void
	 */
	private function resolvePrimaryKey()
	{
		if (empty($this->primaryKey))
		{
			$this->increments('id');
		}
	}

	/**
	 *	Make any changes on this object and their children unchangeable
	 *
	 *	@return	void
	 */
	public function ensureFields()
	{
		foreach ($this->fields as $field)
		{
			$field->realize();
		}
		$this->resolveUnresolvedForeignKeys();
		foreach ($this->foreign_keys as $foreign)
		{
			$foreign->realize();
		}
	}

	/**
	 *	Performs table migration tasks
	 *
	 *	@return	void
	 */
	public function migrate()
	{
		$conn = $this->database->getConnection();

		if (!empty($conn))
		{
			$tableName = $this->getName();

			$fields = [];
			foreach ($this->fields as $n => $f)
			{
				$fields[] = [
					'name' => $n,
					'type' => Value::toString($f->type),
					'length' => $f->size,
					'nullable' => $f->isNullable,
				];
			}

			$f = $this->primaryKey;
			$pk = [
				'name' => $f->name,
				'type' => Value::toString($f->type),
				'length' => $f->size,
				'nullable' => $f->isNullable,
			];

			$fk = [];
			foreach ($this->foreign_keys as $n => $f)
			{
				$fk[] = [
					'name' => 'fk_' . $n,
					'key' => $f->name,
					'foreign_table' => $f->foreignTable,
					'foreign_index' => $f->foreignTableKey,
				];
			}

			$ddl = $conn->dialect->createTable($tableName, $fields, $pk, $fk);

			logit('migrate::test for table('.$tableName.')', $ddl);
		}
	} 

}


