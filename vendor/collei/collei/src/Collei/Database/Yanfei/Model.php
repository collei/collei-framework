<?php
namespace Collei\Database\Yanfei;

use InvalidArgumentException;
use Collei\Database\DatabaseException;
use Collei\Database\Meta\DS;
use Collei\Database\Meta\Table;
use Collei\Database\Query\DB;
use Collei\Database\Query\Select;
use Collei\Database\Query\Clauses\Where;
use Collei\Database\Relations\OneToMany;
use Collei\Database\Relations\ManyToMany;
use Collei\Pacts\Jsonable;
use Collei\Utils\Arr;
use Collei\Utils\Str;
use Collei\Utils\Calendar\Date;

/**
 *	Encapsulates a given database Model, its attributes and methods
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
abstract class Model implements Jsonable
{
	/**
	 *	@var \Collei\Database\Connections\Connection $connection
	 */
	private $connection = null;

	/**
	 *	@var \Collei\Database\Query\Select $select
	 */
	private $select = null;

	/**
	 *	@var array $cache
	 */
	private $cache = [];

	/**
	 *	@var bool $is_changed
	 */
	private $is_changed = false;

	/**
	 *	@var bool $is_new
	 */
	private $is_new = true;

	/**
	 *	@var array $attributes
	 */
	protected $attributes = [];

	/**
	 *	@var array $fillable
	 */
	protected $fillable = [];

	/**
	 *	@var array $readonly
	 */
	protected $readonly = [];

	/**
	 *	Verify if $that is equals to the current instance
	 *
	 *	@param	\Collei\Database\Yanfei\Model	$that
	 *	@return	bool
	 */
	public final function equals(Model $that = null)
	{
		if (is_null($that))
		{
			return false;
		}
		if (!($that instanceof static))
		{
			return false;
		}
		if (count($this->attributes) != count($that->attributes))
		{
			return false;
		}

		foreach ($this->attributes as $n => $v)
		{
			if ($v != $that->attributes[$n])
			{
				return false;
			}
		}

		return true;
	}

	/**
	 *	Establishes a link between the Model instance and the database connection
	 *
	 *	@return	void
	 */
	private function tie()
	{
		$name = $this->getTable();
		$table = DS::getTable($name);

		if (!is_null($table))
		{
			$this->connection = $table->getDatabase()->getConnection();
		}
	}

	/**
	 *	Returns the table name from the model class name
	 *
	 *	@return	string
	 */
	private function nameFromModel()
	{
		return $this->entityNameFromModel() . 's';
	}

	/**
	 *	Calculates and returns the table name from the model class name
	 *
	 *	@return	string
	 */
	private function entityNameFromModel()
	{
		$classname = get_class($this);

		if ($pos = strrpos($classname, '\\'))
		{
			$classname = substr($classname, $pos + 1);
		}

		return Str::toSnake($classname);
	}

	/**
	 *	Attaches a given query
	 *
	 *	@param	mixed	$select
	 */
	protected function setSourceQuery($select)
	{
		$this->select = $select;
	}

	/**
	 *	Returns the table name
	 *
	 *	@return	string
	 */
	public function getTable()
	{
		return $this->table ?? $this->nameFromModel();
	}

	/**
	 *	Returns the related attributes (sub-entites and so on)
	 *	defined as it by the user
	 *
	 *	@return	array
	 */
	public function getRelated()
	{
		return $this->related ?? [];
	}

	/**
	 *	Returns the entity name
	 *
	 *	@return	string
	 */
	public function getEntity()
	{
		return $this->entityNameFromModel();
	}

	/**
	 *	Returns the name of the primary key field
	 *
	 *	@return	string
	 */
	public function getKey()
	{
		return $this->primaryKey ?? 'id';
	}

	/**
	 *	Returns the data type of the primary key field
	 *
	 *	@return	string
	 */
	public function getKeyType()
	{
		return $this->keyType ?? 'integer';
	}

	/**
	 *	Returns the name of the created_at field
	 *
	 *	@return	string
	 */
	protected function getCreatedAt()
	{
		return $this->created_at ?? 'created_at';
	}

	/**
	 *	Returns the name of the updated_at field
	 *
	 *	@return	string
	 */
	protected function getUpdatedAt()
	{
		return $this->updated_at ?? 'updated_at';
	}

	/**
	 *	Returns whether there is an incrementing field in the model's table
	 *
	 *	@return	bool
	 */
	protected function isIncrementing()
	{
		return $this->incrementing ?? true;
	}

	/**
	 *	Returns whether $fieldName is writeable or not
	 *
	 *	@return	bool
	 */
	protected function isFillable(string $fieldName)
	{
		return in_array($fieldName, $this->fillable ?? []);
	}

	/**
	 *	Returns whether $fieldName is readonly
	 *
	 *	@return	bool
	 */
	protected function isReadonly(string $fieldName)
	{
		return in_array($fieldName, $this->readonly ?? []);
	}

	/**
	 *	Returns whether $fieldName is readonly
	 *
	 *	@return	bool
	 */
	protected function hasField(string $fieldName)
	{
		return ($this->getKey() == $fieldName)
			|| in_array($fieldName, $this->fillable ?? [])
			|| in_array($fieldName, $this->readonly ?? []);
	}

	/**
	 *	Returns whether there are timestamp control fields in the model's table
	 *
	 *	@return	bool
	 */
	protected function hasTimestamps()
	{
		return $this->timestamps ?? false;
	}

	/**
	 *	Returns the specified attribute
	 *
	 *	@param	string	$name
	 *	@return	mixed
	 */
	protected final function getAttribute($name)
	{
		if (array_key_exists($name, $this->attributes))
		{
			if ($this->attributes[$name] instanceof \DateTime)
			{
				return $this->attributes[$name]->format('Y-m-d H:i:s');
			}
			return $this->attributes[$name];
		}
	}

	/**
	 *	Sets the value of the specified attribute
	 *
	 *	@param	string	$name
	 *	@param	mixed	$value
	 *	@return	void
	 */
	protected final function setAttribute(string $name, $value)
	{
		if ($name == $this->getKey())
		{
			$this->is_new = false;
		}
		//
		if (array_key_exists($name, $this->attributes))
		{
			if ($this->attributes[$name] instanceof \DateTime)
			{
				$cal = Date::toDateObject($value);

				if (!is_null($cal))
				{
					$this->attributes[$name]
						->setDate($cal->year, $cal->month, $cal->day)
						->setTime($cal->hour, $cal->minute, $cal->second);
				}
			}
			elseif ($this->attributes[$name] instanceof Date)
			{
				$cal = Date::toDateObject($value);

				if (!is_null($cal))
				{
					$this->attributes[$name] = $cal;
				}
			}
			else
			{
				$this->attributes[$name] = $value;
			}
		}
		else
		{
			$this->attributes[$name] = $value;
		}
	}

	/**
	 *	Returns if the specified attribute exists
	 *
	 *	@param	string	$name
	 *	@return	bool
	 */
	protected final function hasAttribute(string $name)
	{
		return array_key_exists($name, $this->attributes);
	}

	/**
	 *	Returns if the model was created from zero or not
	 *
	 *	@return	bool
	 */
	protected final function isNew()
	{
		return $this->is_new;
	}

	/**
	 *	Returns values by calling no-argument methods as attributes
	 *	and also retrieves attributes.
	 */
	public function __get($name)
	{
		if (method_exists($this, $name))
		{
			if (!isset($this->cache[$name]))
			{
				$this->cache[$name] = $this->$name();
			}
			return $this->cache[$name];
		}
		return $this->getAttribute($name);
	}

	/**
	 *	Sets attributes when they exist
	 */
	public function __set($name, $value)
	{
		if ($name != $this->getKey())
		{
			$this->setAttribute($name, $value);
		}
	}

	/**
	 *	Asks whether an attribute exists (used by isset() function)
	 */
	public function __isset(string $name)
	{
		return $this->hasAttribute($name);
	}

	/**
	 *	Used internally by PHP debug functions.
	 *	Helps making results more clear and concise. 
	 */
	public function __debugInfo()
	{
		$result = [];
		$id_name = $this->getKey();

		if (!array_key_exists($id_name, $this->attributes))
		{
			$result[$id_name] = null;
		}

		foreach ($this->attributes as $n => $v)
		{
			$result[$n] = $v;
		}

		foreach ($this->cache as $n => $v)
		{
			$result['cached:' . $n] = $v;
		}

		return $result;
	}

	/**
	 *	Send changes to the database
	 *
	 *	@return	void
	 */
	public function save()
	{
		return Model::insertOrUpdate($this);
	}

	/**
	 *	Removes the associated data from the database
	 *
	 *	@return	mixed
	 */
	public function remove()
	{
		return Model::delete($this);
	}

	/**
	 *	A convenient mode for using with Model::from([field => 'value']) queries
	 *	that may return either Model or ModelResult instances.
	 *
	 *	e.g.: get the first person of list
	 *
	 *	$workers = Employee::from(['city' => 'New York']);
	 *	$first = $workers->firstResult();
	 *
	 *	@return	instanceof Model
	 */
	public function firstResult()
	{
		return $this;
	}

	/**
	 *	Performs database insertion or update
	 *
	 *	@param	\Collei\Database\Yanfei\Model	$model
	 *	@return	mixed
	 */
	protected static function insertOrUpdate(Model $model)
	{
		$table = $model->getTable();
		$key = $model->getKey();
		$timeCreated = $model->getCreatedAt();
		$timeUpdated = $model->getUpdatedAt();
		$data = Arr::rekey(
			Arr::exceptKeys($model->attributes, [ $key, $timeCreated, $timeUpdated ]),
			function ($arrayKey) { return Str::toSnake($arrayKey); }
		);

		if (!$model->isNew() && $model->hasAttribute($key))
		{
			$updater = DB::update($table);

			foreach ($data as $n => $v)
			{
				$updater->set($n, $v);
			}

			return $updater->where()
				->is($key, $model->$key)
				->execute();
		}
		else
		{
			if ($model->hasTimestamps())
			{
				$data[$model->getUpdatedAt()] = ':updated_at'; 
			}

			$model->setAttribute(
				$key,
				DB::into($table)->insert($data)->done()
			);

			return $model;
		}
	}

	/**
	 *	Performs data deletion
	 *
	 *	@param	\Collei\Database\Yanfei\Model	$model
	 *	@return	mixed
	 */
	protected static function delete(Model $model)
	{
		$table = $model->getTable();
		$key = $model->getKey();

		if (!$model->isNew() && $model->hasAttribute($key))
		{
			$eraser = DB::delete($table);

			return $eraser->where()
				->is($key, $model->$key)
				->execute();
		}

		return false;
	}

	/**
	 *	Returns the name of the table
	 *
	 *	@return	string
	 */
	protected static function askTableName()
	{
		return (new static())->getTable();
	}

	/**
	 *	Returns the name of the table key
	 *
	 *	@return	string
	 */
	protected static function askTableKey()
	{
		return (new static())->getKey();
	}

	/**
	 *	Returns the data as a specific or generic Model instance
	 *
	 *	@return	instanceof \Collei\Database\Yanfei\Model
	 */
	protected static function fillModel(array $row)
	{
		$piece = null;

		if (static::class !== self::class)
		{
			$piece = new static();
		}
		else
		{
			$piece = new NullModel();
		}

		foreach ($row as $n => $v)
		{
			$piece->setAttribute(Str::toCamel($n), $v);
		}

		return $piece;
	}

	/**
	 *	Returns the data as a specific or generic Model instance
	 *
	 *	@return	instanceof \Collei\Database\Yanfei\Model
	 */
	public static function fill(array $row)
	{
		return static::fillModel($row);
	}

	/**
	 *	Returns the data as a list of specific or generic Model instances
	 *
	 *	@param	array	$rowset
	 *	@param	bool	$asCollection
	 *	@param	string	$collectionType
	 *	@return	\Collei\Database\Yanfei\Model
	 */
	protected static function fillModelList(
		array $rowset,
		bool $asCollection = false,
		string $collectionType = Model::class
	)
	{
		$list = [];
		
		foreach ($rowset as $row)
		{
			$list[] = $collectionType::fillModel($row);
		}

		if ($asCollection)
		{
			return ModelResult::fromTypedArray($list, $collectionType, false);
		}

		return $list;
	}

	protected static function tableFromModel($model)
	{
		return (new $model)->askTableName();
	}

	/**
	 *	Returns a Model instance from the database $id.
	 *
	 *	@param	int	$id
	 *	@return	\Collei\Database\Yanfei\Model
	 */
	public static function findById(int $id)
	{
		return static::fromId($id);
	}

	/**
	 *	Returns a Model instance from the database $id
	 *
	 *	@param	int	$id
	 *	@return	\Collei\Database\Yanfei\Model
	 */
	public static function fromId(int $id)
	{
		$model = new static();
		$key = $model->getKey();

		$data = DB::from($model->getTable())
					->select('*')
					->where()->is($key, $id)
					->gather();

		if (!is_null($data))
		{
			if (count($data) >= 1)
			{
				return static::fillModel($data[0]);
			}
		}

		return null;
	}

	/**
	 *	Returns a Model instance - or a collection of Model instances - from specific data 
	 *
	 *	@param	mixed	$data	integer index of the record, or query fields to be matched
	 *	@param	int		$rowsPerPage	number of results per page
	 *	@param	int		$page	which page to query
	 *	@return	\Collei\Database\Yanfei\Model|\Collei\Database\Yanfei\ModelResult
	 */
	public static function from($data, int $rowsPerPage = null, int $page = null)
	{
		if (is_int($data) || is_numeric($data))
		{
			return static::fromId((int)(double)$data);
		}
		else
		{
			$first = true;
			$query = DB::from(static::askTableName())
						->select('*')
						->pageSize($rowsPerPage)
						->page($page)
						->where();

			foreach ($data as $n => $v)
			{
				if (!$first)
				{
					$query->and();
				}
				$query->is($n, $v);
			}

			$data = $query->gather();

			if (!is_null($data))
			{
				$count = count($data);
				if ($count == 1)
				{
					return static::fillModel($data[0]);
				}
				elseif ($count > 1)
				{
					return static::fillModelList($data, true, static::class);
				}
			}

			return null;
		}
	}

	/**
	 *	Creates a new instance of the given Model
	 *
	 *	@return	\Collei\Database\Yanfei\Model
	 */
	public static function new()
	{
		return new static();
	}

	/**
	 *	Returns the number of records in the table associated with the given Model
	 *
	 *	@return	int
	 */
	public static function count()
	{
		$info = DB::from(static::askTableName())
					->select('COUNT(*) AS [numberofrows]')
					->gather(true);

		return $info[0]->numberofrows;
	}

	/**
	 *	Returns the number of records in the table associated with the given Model
	 *
	 *	@return	int
	 */
	public static function pageCount(int $pageSize)
	{
		$rowCount = static::count();
		$pageSize = ($pageSize < 1) ? $rowCount : $pageSize;

		$pages = \intdiv($rowCount, $pageSize);

		if (($rowCount % $pageSize) > 0)
		{
			++$pages;
		}

		return $pages;
	}

	/**
	 *	Returns all database rows as a collection of Model instances.
	 *
	 *	Order by may be issued as follows:
	 *		Person::all('name asc', 'date_birth desc')
	 *
	 *	@param	string	...$orderBy
	 *	@return	\Collei\Database\Yanfei\ModelResult
	 */
	public static function all(string ...$orderBy)
	{
		$query = DB::from(static::askTableName())
					->select('*');

		foreach ($orderBy as $ord)
		{
			$elements = '';

			if (preg_match('/^(\s*(\w+(\.\w+)*)\s+(asc|desc)\s*)$/i', $ord, $elements))
			{
				$query->orderBy(
					$elements[2],
					strtolower($elements[4] ?? '') == 'desc'
				);
			}
		}

		$query = $query->gather();

		if (!is_null($query))
		{
			return self::fillModelList($query, true, static::class);
		}
		return ModelResult::fromEmpty();
	}

	/**
	 *	Returns some database rows (amounts based on pagination)
	 *	as a collection of Model instances.
	 *
	 *	Order by may be issued as follows:
	 *		Person::paged(1, 10, 'name asc', 'date_birth desc')
	 *
	 *	@param	int	$page
	 *	@param	int	$rowsPerPage
	 *	@param	string	...$orderBy
	 *	@return	\Collei\Database\Yanfei\ModelResult
	 */
	public static function paged(int $page, int $rowsPerPage = null, string ...$orderBy)
	{
		$page = ($page > 0) ? $page : 1;
		$rowsPerPage = $rowsPerPage ?? 10;
		$rowsPerPage = ($rowsPerPage > 0) ? $rowsPerPage : 10;

		//logit(__METHOD__, print_r([$page,$rowsPerPage,$orderBy],true));

		$query = DB::from(static::askTableName())
					->select('*')
					->page($page)
					->pageSize($rowsPerPage);

		foreach ($orderBy as $ord)
		{
			$elements = '';

			if (preg_match('/^(\s*(\w+(\.\w+)*)\s+(asc|desc)\s*)$/i', $ord, $elements))
			{
				$query->orderBy(
					$elements[2],
					strtolower($elements[4] ?? '') == 'desc'
				);
			}
		}

		$query = $query->gather();

		if (!is_null($query))
		{
			return self::fillModelList($query, true, static::class);
		}
		return ModelResult::fromEmpty();
	}

	/**
	 *	Creates and returns a where clause. It accepts an optional where subclause
	 *
	 *	@param	mixed	$left = null
	 *	@param	mixed	$middle = null
	 *	@param	mixed	$right = null
	 *	@return	\Collei\Database\Query\Clauses\Where
	 */
	public function where($left = null, $middle = null, $right = null)
	{
		$this->select = DB::from($this->getTable());
		return Where::createWith($this->select)
			->where($left, $middle, $right);
	}

	/**
	 *	Returns a select clause after the join performed
	 *
	 *	@return	\Collei\Database\Query\Select
	 */
	public static function join($anotherModel, string $ownedKey = null)
	{
		if (!is_subclass_of($anotherModel, Model::class))
		{
			throw new InvalidArgumentException(
				$anotherModel . ' is not a subclass of ' . Model::class . '.'
			);
		}

		$atn = static::askTableName();
		$jtn = $anotherModel::askTableName();

		return DB::from($atn)
			->select($atn . '.*')
			->join($jtn)->on(static::askTableKey(), $ownedKey);
	}

	// keep result caching
	protected $relationCache = [
		'has_many' => [],
		'belongs_to' => [],
		'belongs_to_many' => []
	];

	/**
	 *	Returns a ModelResult collection of all child Models related to the current Model
	 *
	 *	@param	mixed	$relatedModelClass
	 *	@param	string	$foreignKey
	 *	@param	string	$localKey
	 *	@return	\Collei\Database\Yanfei\ModelResult
	 */
	public function hasMany(
		$relatedModelClass,
		string $foreignKey = null, string $localKey = null
	)
	{
		if (!is_subclass_of($relatedModelClass, Model::class))
		{
			throw new InvalidArgumentException(
				$relatedModelClass . ' is not a subclass of ' . Model::class . '.'
			);
		}

		if (!isset($this->relationCache['has_many'][$relatedModelClass]))
		{
			$oneToMany = new OneToMany(
				$this,
				new $relatedModelClass,
				$foreignKey,
				$localKey
			);

			$results = $oneToMany->fetch();

			$this->relationCache['has_many'][$relatedModelClass] =
				static::fillModelList($results, true, $relatedModelClass);
		}

		return $this->relationCache['has_many'][$relatedModelClass];
	}

	/**
	 *	Returns the parent Model related to the current Model
	 *
	 *	@param	mixed	$relatedModelClass
	 *	@param	string	$localForeign
	 *	@return	\Collei\Database\Yanfei\Model
	 */
	public function belongsTo($relatedModelClass, string $localForeign = null)
	{
		if (!is_subclass_of($relatedModelClass, Model::class))
		{
			throw new InvalidArgumentException(
				$relatedModelClass . ' is not a subclass of ' . Model::class . '.'
			);
		}

		if (!isset($this->relationCache['belongs_to'][$relatedModelClass]))
		{
			$localForeign = $localForeign ?? ((new $relatedModelClass)->getEntity() . '_id');
			$localForeign = Str::toCamel($localForeign);
			$localForeignId = $this->$localForeign;

			$this->relationCache['belongs_to'][$relatedModelClass] =
				$relatedModelClass::fromId($localForeignId);
		}

		return $this->relationCache['belongs_to'][$relatedModelClass];
	}

	/**
	 *	Returns a ModelResult collection of all parent/brother Models related to the current Model
	 *
	 *	@param	mixed	$relatedModelClass
	 *	@param	string	$intermediate
	 *	@param	string	$foreignNear
	 *	@param	string	$foreignFar
	 *	@return	\Collei\Database\Yanfei\ModelResult
	 */
	public function belongsToMany(
		$relatedModelClass,
		string $intermediate = null,
		string $foreignNear = null,
		string $foreignFar = null
	){
		if (!is_subclass_of($relatedModelClass, Model::class))
		{
			throw new InvalidArgumentException(
				$relatedModelClass . ' is not a subclass of ' . Model::class . '.'
			);
		}

		if (!isset($this->relationCache['belongs_to_many'][$relatedModelClass]))
		{
			$manyToMany = new ManyToMany(
				$this,
				new $relatedModelClass(),
				$intermediate,
				$foreignNear,
				$foreignFar
			);

			$this->relationCache['belongs_to_many'][$relatedModelClass] = 
				static::fillModelList(
					$manyToMany->fetch(), true, 	$relatedModelClass
				);
		}

		return $this->relationCache['belongs_to_many'][$relatedModelClass];
	}

	/**
	 *	Builds and instantiates a Model
	 *
	 */
	public final function __construct()
	{
		$this->tie();
	}

	/**
	 *	Retrieves the entity (and related ones) as JSON string
	 *
	 *	@param	string	...$except	fields and relations to exclude from result
	 *	@return	string
	 */
	public function asJson(string ...$except)
	{
		$fields = [];

		foreach ($this->attributes as $n => $v)
		{
			if (!in_array($n, $except, true))
			{
				$fields[$n] = $v;
			}
		}

		$metafields = $this->getRelated();

		foreach ($metafields as $n => $v)
		{
			$proof = $n;
			$callee = $v;
			$child = [];

			if (is_numeric($n))
			{
				$proof = $v;
			}

			if (!in_array($proof, $except, true))
			{
				if (method_exists($this, $callee))
				{
					$child = $this->{$callee}();
				}
				else
				{
					$child = $this->$callee;
				}

				$jsonable = (
					is_subclass_of($child, Model::class) ||
					is_subclass_of($child, ModelResult::class)
				);

				if ($jsonable)
				{
					$fields[$proof] = json_decode($child->asJson());
				}
				else
				{
					$fields[$proof] = $child;
				}
			}
		}

		return json_encode($fields);
	}

	/**
	 *	converts the object data to Json string
	 *
	 *	@return	string
	 */
	public function toJson()
	{
		return $this->asJson();
	}

}



/**
 *	A dummy Model for comparison
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class NullModel extends Model
{
}


