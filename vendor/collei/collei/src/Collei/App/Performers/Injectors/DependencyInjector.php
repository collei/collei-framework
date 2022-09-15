<?php 
namespace Collei\App\Performers\Injectors;

use Collei\Exceptions\ColleiClassException;
use ReflectionClass;

/**
 *	Encapsulates a dependency injector
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2022-09-06
 */
class DependencyInjector
{
	/**
	 *  bound class instantiators and constructors
	 */
	private static $BOUND = [];

	/**
	 *	@var array $reflector
	 */
	protected $reflector = [
		'class' => null,
		'function' => null,
		'parameters' => null,
	];

	/**
	 *	@var array $called
	 */
	protected $called = [
		'instance' => null,
		'class' => '',
		'method' => '',
		'constructor' => false,
	];

	/**
	 *	@var array $parameters
	 */
	protected $parameters = [];

	/**
	 *	@var array $values
	 */
	protected $values = [];

	/**
	 *	@var bool $readyToCall
	 */
	protected $readyToCall = false;

	/**
	 *	Initializes the needed reflector engines for the work
	 *
	 *	@throws	\Exception
	 *	@param	string|object	$classOrInstance
	 *	@param	string	$method = null
	 *	@return	null	 
	 */
	private function initReflect($classOrInstance, string $method = null)
	{
		if (
			$this->called['constructor'] = is_null($method) && is_string($classOrInstance)
		) {
			$this->called['instance'] = null;
			$this->called['class'] = $classOrInstance;
			$this->called['method'] = '__construct';
			$this->reflector['class'] = (
				$reflClass = new ReflectionClass($classOrInstance)
			);
			$this->reflector['function'] = (
				$reflFunc = $reflClass->getConstructor()
			);
		} elseif (is_string($classOrInstance)) {
			$this->called['instance'] = null;
			$this->called['class'] = $classOrInstance;
			$this->called['method'] = $method;
			$this->reflector['class'] = (
				$reflClass = new ReflectionClass($classOrInstance)
			);
			$this->reflector['function'] = (
				$reflFunc = $reflClass->getMethod($method)
			);
		} else {
			$this->called['instance'] = $classOrInstance;
			$this->called['class'] = get_class($this->instance);
			$this->called['method'] = $method;
			$this->reflector['class'] = (
				$reflClass = new ReflectionClass($classOrInstance)
			);
			$this->reflector['function'] = (
				$reflFunc = $reflClass->getMethod($method)
			);
		}
		//
		$this->reflector['parameters'] = $reflFunc->getParameters();
	}

	/**
	 *	Returns info on the registered type, false otherwise
	 *
	 *	@static
	 *	@param	string	$type
	 *	@return	array|bool
	 */
	protected static function boundInfo(string $type)
	{
		return self::$BOUND[$type] ?? false;	
	}

	/**
	 *	If $type was previously registered, EITHER instantiates $type
	 *	OR calls the related static method.
	 *	If $type is not found, returns false.
	 *
	 *	@static
	 *	@param	mixed	$type
	 *	@param	string	$method = null
	 *	@return	null	 
	 */
	protected static function staticOrNew($type)
	{
		if ($bound = self::boundInfo($type)) {
			if ($bound['method'] == '__construct') {
				return new $type();
			}
			//
			if ($bound['static']) {
				$staticMethod = $type . '::' . $bound['method'];
				//
				return $staticMethod();
			}
			//
			return false;
		}
		//
		return false;
	}

	/**
	 *	Initializes a new Injector
	 *
	 *	@throws	\Collei\Exceptions\ColleiClassException
	 *	@param	mixed	$classOrInstance
	 *	@param	string	$method = null
	 */
	public function __construct(
		$classOrInstance,
		string $method = null
	) {
		try {
			$this->initReflect($classOrInstance, $method);
		} catch (Exception $ex) {
			$method = $this->called['method'];
			$class = $this->called['class'];
			throw new ColleiClassException(
				"Method {$method} does not exist on Class {$class}."
			);
		}
	}

	/**
	 *	Returns an array of \ReflectionParameter which refer
	 *	to the method parameters
	 *
	 *	@return	array[\ReflectionParameter]
	 */
	public function getParameterReflectors()
	{
		return $this->reflector['parameters'];
	}

	/**
	 *	Register the given $class and its $method (if any).
	 *	Setting $method to null == '__construct'.
	 *
	 *	@static
	 *	@param	string	$class
	 *	@param	string	$method = null
	 *	@param	bool	$static = false
	 *	@return	void
	 */
	public static function bindClass(
		string $class,
		string $method = null,
		bool $static = false
	) {
		if (empty($method)) {
			$method = '__construct';
		}
		//
		self::$BOUND[$class] = [
			'method' => $method,
			'static' => $static
		];
	}

	/**
	 *	Register the given $class and its $method (if any).
	 *	Setting $method to null == '__construct'.
	 *
	 *	@param	string	$class
	 *	@param	string	$method = null
	 *	@param	bool	$static = false
	 *	@return	self
	 */
	public function bind(
		string $class,
		string $method = null,
		bool $static = false
	) {
		self::bindClass($class, $method, $static);
		//
		return $this;
	}

	/**
	 *	Registers a parameter value to be passed to the method caller.
	 *
	 *	@param	string	$parameterName
	 *	@param	mixed	$value = null
	 *	@return	self
	 */
	public function addValue(string $parameterName, $value = null)
	{
		$this->values[$parameterName] = $value;
		//
		return $this;
	}

	/**
	 *	Prepares the parameters and build dependencies on the fly
	 *	wherever they are needed.
	 *
	 *	@throws	\Collei\Exceptions\ColleiClassException
	 *	@return	self
	 */
	public function fetch()
	{
		if ($this->readyToCall) {
			return $this;
		}
		//
		$this->methodParameters = [];
		$methodName = $this->called['method'];
		//
		foreach ($this->reflector['parameters'] as $reParam) {
			$paramName = $reParam->getName();
			$paramType = $reParam->getType();
			//
			$paramType = is_null($paramType) ? '' : $paramType->getName();
			//
			if (array_key_exists($paramType, $this->values)) {
				$value = $this->values[$paramType];
				//
				if (is_a($value, $paramType)) {
					$this->parameters[] = $value;
				} else {
					$typeName = gettype($value);
					//
					throw new ColleiClassException(
						"Method $methodName, argument $paramName: "
						. "expected $paramType but found $typeName instead..."
					);
				}
			} elseif (array_key_exists($paramName, $this->values)) {
				$value = $this->values[$paramName];
				//
				if (is_a($value, $paramType)) {
					$this->parameters[] = $value;
				} else {
					$typeName = gettype($value);
					//
					throw new ColleiClassException(
						"Method $methodName, argument $paramName: "
						. "expected $paramType but found $typeName instead."
					);
				}
			} elseif ($bound = self::boundInfo($paramType)) {
				$this->parameters[] = self::staticOrNew($paramType);
			} elseif (class_exists($paramType)) {
				$this->parameters[] = self::staticOrNew($paramType);
			} elseif ($reParam->isOptional()) {
				$this->parameters[] = $reParam->getDefaultValue();
			} elseif ($reParam->allowsNull()) {
				$this->parameters[] = null;
			} else {
				throw new ColleiClassException(
					"Method $methodName, argument $paramName: "
					. "expected $paramType, null not valid for mandatory."
				);
			}
		}
		//
		$this->readyToCall = true;
		//
		return $this;
	}

	/**
	 *	Executes the call to the given method at the constructor.
	 *
	 *	@return	mixed
	 */
	public function call()
	{
		$this->fetch();
		//
		$this->readyToCall = false;
		//
		if ($this->called['constructor']) {
			return $this->reflector['class']->newInstanceArgs(
				$this->parameters
			);
		}
		//
		if ($this->reflector['function']->isStatic()) {
			return $this->reflector['function']->invokeArgs(
				null,
				$this->parameters
			);
		}
		//
		return $this->reflector['function']->invokeArgs(
			$this->called['instance'],
			$this->parameters
		);
	}

}

