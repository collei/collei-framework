<?php 
namespace Collei\App\Performers\Injectors;

use Collei\App\Performers\Injectors\DependencyInjector;
use Collei\Exceptions\ColleiClassException;
use ReflectionClass;

/**
 *	Encapsulates a Parameter injector
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2022-08-30
 */
class ParameterInjector extends DependencyInjector
{
	/**
	 *  basic types and default values
	 */
	private const ATOMIC = [
		'array' => [],
		'callable' => null,
		'bool' => false,
		'float' => 0.0,
		'int' => 0,
		'string' => '',
	];

	/**
	 *  these are easily coercible types
	 */
	private const COERCIBLE = [
		'bool:int', 'bool:float', 'bool:string',
		'int:bool', 'int:float', 'int:string',
		'float:bool', 'float:int', 'float:string',
		'string:bool', 'string:int', 'string:float',
	];

	private function isCoercibleTo(string $typeFrom, string $typeTo)
	{
		$typeFrom = strtolower($typeFrom);
		$typeTo = strtolower($typeTo);
		//
		return in_array("{$typeFrom}:{$typeTo}", self::COERCIBLE)
			|| in_array($typeTo, ['array','callable','closure']);
	}

	private function coerce($value, string $typeFrom, string $typeTo)
	{
		$typeFrom = strtolower($typeFrom);
		$typeTo = strtolower($typeTo);
		//
		if ($typeTo == 'array') {
			return array($value);
		} elseif (($typeTo == 'callable') || ($typeTo == 'closure')) {
			$inner = $value;
			return (function(...$params) use ($inner) {
				return $inner;
			});
		}
		//
		switch ("{$typeFrom}:{$typeTo}") {
			case 'bool:int':
				return $value ? 1 : 0;
			case 'bool:float':
				return $value ? 1.0 : 0.0;
			case 'bool:string':
				return $value ? 'True' : 'False';
			case 'int:bool':
				return $value != 0;
			case 'int:float':
				return (float)$value;
			case 'int:string':
				return "{$value}";
			case 'float:bool':
				return $value != 0;
			case 'float:int':
				return (int)$value;
			case 'float:string':
				return "{$value}";
			case 'string:bool':
				return in_array(strtolower($value), ['true', 'yes', '1']);
			case 'string:int':
				return is_numeric($value) ? (int)(float)$value : 0;
			case 'string:float':
				return is_numeric($value) ? (float)$value : 0.0;
		}
		//
		return;
	} 



	public function __construct(
		$classOrInstance,
		string $method = null
	) {
		parent::__construct($classOrInstance, $method);
	}





	public function fetch()
	{
		$this->methodParameters = [];
		$methodName = $this->called['method'];
		//
		foreach ($this->reflector['parameters'] as $reParam) {
			$paramName = $reParam->getName();
			$paramType = $reParam->getType();
			$paramType = is_null($paramType) ? '' : $paramType->getName();
			//
			if (array_key_exists($paramName, $this->values)) {
				$value = $this->values[$paramName];
				$typeName = gettype($value);

				$valueType = gettype($this->parameterValues[$reParamName]);

				//
				if ($typeName == $paramType)) {
					$this->parameters[] = $value;
				} elseif (is_a($value, $paramType)) {
					$this->parameters[] = $value;
				} else {
					$typeName = gettype($value);
					//
					throw new ColleiClassException(
						"Method $methodName, argument $paramName: "
						. "expected $paramType but found $typeName instead."
					);
				}
			/****/
			/****/
			} elseif (!is_a($paramType, 'stdClass')) {
				if ($typeName == $paramType) {
					$this->parameters[] = $value;
				} elseif ($this->isCoercibleTo($typeName, $paramType)) {
					$this->parameters[] = $this->coerce(
						$value, $typeName, $paramType
					);
				} elseif ($reParam->isOptional()) {
					$this->parameters[] = $reParam->getDefaultValue();
				} elseif (array_key_exists($paramType, self::ATOMIC)) {
					$this->parameters[] = self::ATOMIC[$paramType];
				}
			/****/
			/****/
			} elseif ($bound = self::boundInfo($paramType)) {
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
		return $this;
	}

}

