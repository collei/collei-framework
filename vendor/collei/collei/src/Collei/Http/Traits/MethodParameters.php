<?php 
namespace Collei\Http\Traits;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Exception;

	
/**
 *	Encapsulates the servlet response
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-05-xx
 */
trait MethodParameters
{
	/**
	 *	Filler shorthand 
	 *
	 *	@param	string	$name
	 *	@param	string	$type
	 *	@param	mixed	$value
	 *	@return	array
	 */
	protected function parameterSlot(string $name, string $type, $value = null, bool $optional = false)
	{
		return [
			'name' => $name,
			'type' => $type,
			'value' => $value,
			'optional' => $optional,
		];
	} 

	/**
	 *	Fill parameters for a method, with parameter injection resolution 
	 *
	 *	@param	array	$refParams
	 *	@param	array	$classList
	 *	@return	array
	 */
	protected function internalParameterFiller(array $refParams, array $instanceList = [])
	{
		$parameters = [];
		// basic types and default values
		$atomic = [
			'array' => [],
			'callable' => (function(){ return false; }),
			'bool' => false,
			'float' => 0.0,
			'int' => 0,
			'string' => '',
		];

		foreach ($refParams as $ref_parm)
		{
			if (!($ref_parm instanceof ReflectionParameter))
			{
				continue;
			}

			$name = $ref_parm->getName();
			$type = $ref_parm->getType();
			$optional = $ref_parm->isOptional();

			$type = $type->getName() ?? '';

			if (!array_key_exists($type, $atomic))
			{
				if (isset($instanceList[$type]))
				{
					$parameters[] = $this->parameterSlot($name, $type, $instanceList[$type], $optional);
				}
				else
				{
					try
					{
						$ref_x_clas = new ReflectionClass($type);
						$ref_x_cons = $ref_x_clas->getConstructor();
						$num_args = $ref_x_cons->getNumberOfRequiredParameters() ?? -1;

						$new_value = ($num_args < 1)
							? (new $type())
							: ($ref_x_clas->newInstanceWithoutConstructor());

						$parameters[] = $this->parameterSlot($name, $type, $new_value, $optional);
					}
					catch (Exception $x)
					{
						$parameters[] = $this->parameterSlot($name, $type, null, $optional);
					}
				}
			}
			elseif ($optional)
			{
				$parameters[] = $this->parameterSlot(
					$name, $type, $ref_parm->getDefaultValue(), $optional
				);
			}
			elseif (array_key_exists($type, $atomic))
			{
				$parameters[] = $this->parameterSlot(
					$name, $type, $atomic[$type], $optional
				);
			}
			else
			{
				$parameters[] = $this->parameterSlot(
					$name, $type, null, $optional
				);
				//
				logerror('Servlet method: mandatory not present', "Missing argument $name of type $type not present in the request ");
			}
		}

		return $parameters;
	}

	/**
	 *
	 *
	 *
	 *
	 */
	public function fillParameters($class, $method, array $supplementary = [])
	{
		$parameters = [];

		try
		{
			$ref_class = new ReflectionClass($class);
			$ref_method = $ref_class->getMethod($method);
			$ref_params = $ref_method->getParameters();

			$parameters = $this->internalParameterFiller($ref_params, $supplementary);
		}
		catch (Exception $x)
		{
			logerror('Class may not exist', "Missing method: {$class}::{$method} ");
		}

		return $parameters;
	}


}

