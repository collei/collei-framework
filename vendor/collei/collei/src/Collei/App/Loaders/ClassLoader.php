<?php
namespace Collei\App\Loaders;

//require_once PLAT_VENDOR_GROUND . '/collei/collei/src/Collei/Utils/Logging/LogFileTrait.php';


use Collei\Support\Values\Value;
use Collei\Support\Logging\LogFileTrait;
use ReflectionClass;
use ReflectionMethod;
use Exception;
use BadMethodCallException;

/**
 *	Embodies class loader capabilities
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-04-08
 */
class ClassLoader 
{
	use LogFileTrait;

	/**
	 *	@static
	 *	@var array $instances
	 */
	private static $instances = [];

	/**
	 *	Performs parameter filtering on passed $parameters based upon
	 *	method parameters.
	 *
	 *	@static
	 *	@param	ReflectionMethod	$method
	 *	@param	array	$parameters
	 *	@return	array
	 */
	private static function filterParameters(
		ReflectionMethod $method, array $parameters
	) {
		$index = 0;
		$params = [];
		$defaults = [
			[ 'which' => ['bool','boolean'], 'default' => false ],
			[ 'which' => ['int','integer','double','float','real'], 'default' => 0 ],
			[ 'which' => ['array'], 'default' => [] ],
			[ 'which' => ['string'], 'default' => '' ],
		];
		//
		foreach ($method->getParameters() as $param) {
			$name = $param->getName();
			$type = $param->getType();
			//
			$type = (is_null($type) ? '' : $type->getName());
			//
			$val = '';
			//
			if (array_key_exists($name, $parameters)) {
				$val = $parameters[$name];
			}
			//
			if (array_key_exists($index, $parameters)) {
				$val = $parameters[$index];
			} elseif ($param->isOptional()) {
				$val = $param->getDefaultValue();
			} elseif ($param->allowsNull()) {
				$val = null;
			} else {
				throw new BadMethodCallException(
					'Missing argument '
						. $name . ' upon call to '
						. $method.getName()
				);
			}
			//
			if ($type != '') {
				if ($type == gettype($val)) {
					$params[] = $val;
				} else {
					$done = false;
					//
					foreach ($defaults as $def) {
						if ($done = in_array($type, $def['which'])) {
							$params[] = Value::castTo(
								$val, $type, $def['default']
							);
							//
							break;
						}
					}
					//
					if (!$done) {
						throw new BadMethodCallException(
							'Type mismatch for the argument '
								. $name . ' upon call to '
								. $method.getName()
						);
					}
				}
			} else {
				$params[] = $val;
			}
			//
			++$index;
		}
		//
		return $params;
	}

	/**
	 *	Loads classes reflectly
	 *
	 *	@static
	 *	@param	string	$virtual
	 *	@param	array	$parameters = []
	 *	@return	object|null
	 */
	private static function instantiate(string $virtual, array $parameters = [])
	{
		if (empty($virtual)) {
			return null;
		}
		//
		$refl = new ReflectionClass($virtual);
		$construc = $refl->getConstructor();
		$instance = null;
		//
		try {
			if (is_null($construc)) {
				$instance = $refl->newInstanceArgs();
			} else {
				$params = static::filterParameters($construc, $parameters);
				$instance = $refl->newInstanceArgs($params);
			}
		} catch (Exception $ex) {
			return $instance;
		}
		//
		return $instance;
	}

	/**
	 *	Static factory of instances
	 *
	 *	@static
	 *	@param	mixed	$virtual
	 *	@param	array	$params
	 *	@return	mixed
	 */
	public static function load($virtual = null, array $params = [])
	{
		if (isset(static::$instances[$virtual])) {
			return static::$instances[$virtual];
		}
		//
		return static::$instances[$virtual] = static::instantiate(
			$virtual, $params
		);
	}

	/**
	 *	Loads site classes manually. For use of \Collei\App\App class.
	 *
	 *	@static
	 *	@param	mixed	$siteName
	 *	@param	mixed	$className
	 *	@return	bool
	 */
	public static function requireSiteClass(string $siteName, string $className)
	{
		if (class_exists($className)) {
			return false;
		}
		//
		$required = preg_replace(
			'#(\\/+|\\\\+)#',
			DIRECTORY_SEPARATOR,
			PLAT_SITES_GROUND . "/$siteName/$className"	. PLAT_CLASSES_SUFFIX
		);
		//
		if (file_exists($required)) {
			require $required;
			//
			self::log(
				__METHOD__, "required \"$className\" from \"$required\""
			);
			//
			return true;
		}
		//
		return false;
	}

	/**
	 *	Loads manager classes manually. For use of \Collei\App\App class.
	 *
	 *	@static
	 *	@param	mixed	$className
	 *	@return	bool
	 */
	public static function requireManagerClass(string $className)
	{
		if (class_exists($className)) {
			return false;
		}
		//
		$required = preg_replace(
			'#(\\/+|\\\\+)#',
			DIRECTORY_SEPARATOR,
			PLAT_GROUND . "/$className"	. PLAT_CLASSES_SUFFIX
		);
		//
		if (file_exists($required)) {
			require $required;
			//
			self::log(
				__METHOD__, "required \"$className\" from \"$required\""
			);
			//
			return true;
		}
		//
		return false;
	}

}

