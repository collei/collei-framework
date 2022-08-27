<?php

namespace Packinst\Utils;

/**
 *	@author user1441149 from https://stackoverflow.com/
 *	@since 2015-06-14
 *	@link https://stackoverflow.com/a/30833466
 *	@link https://stackoverflow.com/questions/12212796/parse-string-as-array-in-php/30833466#30833466
 *
 *	A class used to convert string representations of php arrays
 *	to a live array without using eval()
 *
 *	as its author said:
 *		"Here is something I have been working on. There are no unit tests yet,
 *		but it seems to work pretty well.
 *		I do not support the use of functions, instantiation of objects,
 *		conditionals, etc. from within the array structure.
 *		I don't want to support those for my use case. But feel free to add
 *		whatever functionality you need."
 *
 */
class ArrayTokenScanner
{
	/**
	 *  @const array ACCEPT
	 */
	protected const TA_GENERAL = [
		T_ARRAY, T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_ARROW,
		T_STRING, T_NUM_STRING, T_LNUMBER, T_DNUMBER
	];

	protected const TA_ATOMIC_STRING = [
		T_STRING, T_NUM_STRING, T_CONSTANT_ENCAPSED_STRING
	];

	/**
	 *  @var array $arrayKeys
	 */
	protected $arrayKeys = [];

	/**
	 *  Performs string scan and further parsing.
	 *  Now accepts multiline strings by making them monoline.
	 *
	 *  e.g. array('foo' => 123, 'bar' => [0 => 123, 1 => 12345])
	 *
	 *  @param  string  $string
	 *  @return array
	 */
	public function scan($string)
	{
		// Transform multiline strings in monoline ones
		$string = $this->quotedStringPreserve('' . $string . '');

		// Remove whitespace and semi colons
		$sanitized = trim($string, " \t\n\r\0\x0B;");
		if (preg_match('/^(\[|array\().*(\]|\))$/', $sanitized))
		{
			if ($tokens = $this->tokenize("<?php {$sanitized}"))
			{
				$this->initialize($tokens);
				return $this->parse($tokens);
			}
		}

		// Given array format is invalid
		throw new InvalidArgumentException("Invalid array format.");
	}

	/**
	 *	Removes newlines from outside strings while preserving
	 *	(encoded) those inside.
	 *
	 *	@param	string	$string
	 *	@return	string
	 */
	protected function quotedStringPreserve(string $string)
	{
		$chars = preg_split('//u', $string, null, PREG_SPLIT_NO_EMPTY);
		$chunks = [];
		$lastQuote = '';
		//
		$delimiters = [ '"', "'" ];
		$targets = [ "\r", "\n" ];
		$alts = [ "\r" => '\r', "\n" => '\n' ];
		//
		foreach ($chars as $char)
		{
			if (in_array($char, $targets))
			{
				if (!empty($lastQuote))
				{
					$chunks[] = $alts[$char];
				}
			}
			else
			{
				$chunks[] = $char;
			}
			//
			if ($lastQuote == $char)
			{
				$lastQuote = '';
			}
			elseif (empty($lastQuote) && in_array($char, $delimiters))
			{
				$lastQuote = $char;
			}
		}
		//
		return implode('', $chunks);
	}

	/**
	 *  Token chain initializer
	 *
	 *  @param  array   $tokens
	 *  @return void
	 */
	protected function initialize(array $tokens)
	{
		$this->arrayKeys = [];
		//
		while($current = current($tokens))
		{
			$next = next($tokens);
			//
			if (($next[0] ?? '') === T_DOUBLE_ARROW)
			{
				$this->arrayKeys[] = $current[1];
			}
		}
	}

	/**
	 *  Shorthand for getting the appropriate key
	 *
	 *  @param  mixed   $assoc
	 *  @param  mixed   &$index
	 *  @return mixed
	 */
	protected function genKey($assoc, &$index)
	{
		return ($assoc !== false)
			? trim($assoc, "'\"")
			: $this->createKey($index);
	}

	/**
	 *  Parser method
	 *
	 *  @param  array   &$tokens
	 *  @return array
	 */
	protected function parse(array &$tokens)
	{
		$array = [];
		$token = current($tokens);
		if (in_array($token[0], [T_ARRAY, T_BRACKET_OPEN]))
		{
			// It's array!
			$assoc = false;
			$index = 0;
			$discriminator = ($token[0] === T_ARRAY)
				? T_ARRAY_CLOSE
				: T_BRACKET_CLOSE;
			//
			while ($token = $this->until($tokens, $discriminator)) 
			{
				// Skip arrow ( => )
				if(in_array($token[0], [T_DOUBLE_ARROW])) {
					continue;
				}

				// Reset associative array key
				if($token[0] === T_COMMA_SEPARATOR) {
					$assoc = false;
					continue;
				}

				// Look for array keys
				$next = next($tokens);
				prev($tokens);
				if ($next[0] === T_DOUBLE_ARROW)
				{
					// Is assoc key
					$assoc = $token[1];
					if(preg_match('/^-?(0|[1-9][0-9]*)$/', $assoc))
					{
						$index = $assoc = (int) $assoc;
					}
					continue;
				}

				// Parse array contents recursively
				if (in_array($token[0], [T_ARRAY, T_BRACKET_OPEN]))
				{
					$array[$this->genKey($assoc, $index)] = $this->parse($tokens);
					continue;
				}

				// Parse atomic string
				if (in_array($token[0], self::TA_ATOMIC_STRING))
				{
					$array[$this->genKey($assoc, $index)] = $this->parseAtomic($token[1]);
				}

				// Parse atomic number
				if (in_array($token[0], [T_LNUMBER, T_DNUMBER]))
				{
					// Check if number is negative
					$prev = prev($tokens);
					$value = $token[1];
					if($prev[0] === T_MINUS) {
						$value = "-{$value}";
					}
					next($tokens);

					$array[$this->genKey($assoc, $index)] = $this->parseAtomic($value);
				}

				// Increment index unless a associative key is used.
				// In this case we want too reuse the current value.
				if (!is_string($assoc))
				{
					$index++;
				}
			}

			return $array;
		}
	}

	/**
	 *  used by parse() method
	 *
	 *  @param  array   $tokens
	 *  @param  int|string  $discriminator
	 *  @return array|false
	 */
	protected function until(array &$tokens, $discriminator)
	{
		$next = next($tokens);
		//
		if ($next === false or $next[0] === $discriminator)
		{
			return false;
		}
		//
		return $next;
	}

	/**
	 *  used by genKey() method and, thence, indirectly,
	 *  by the parse() method
	 *
	 *  @param  mixed   &$index
	 *  @return mixed
	 */
	protected function createKey(&$index)
	{
		do {
			if (!in_array($index, $this->arrayKeys, true))
			{
				return $index;
			}
		} while(++$index);
	}

	/**
	 *  Tokenizer method
	 *
	 *  @param $string
	 *  @return array|false
	 */
	protected function tokenize($string)
	{
		$tokens = token_get_all($string);
		//
		if (is_array($tokens))
		{
			// Filter tokens
			$tokens = array_values(array_filter($tokens, [$this, 'accept']));
			// Normalize token format, make syntax characters
			// look like tokens for consistent parsing
			return $this->normalize($tokens);
		}
		//
		return false;
	}

	/**
	 *  Method used to accept or deny tokens
	 *  so that we only have to deal with the allowed tokens
	 *
	 *  @param array|string $value  A token or syntax character
	 *  @return bool
	 */
	protected function accept($value)
	{
		if (is_string($value))
		{
			// Allowed syntax characters: comma's and brackets.
			return in_array($value, [',', '[', ']', ')', '-']);
		}
		//
		if (!in_array($value[0], self::TA_GENERAL)) 
		{
			// Token did not match requirement.
			// The token is not listed in the collection above.
			return false;
		}
		// Token is accepted.
		return true;
	}
 
	/**
	 *  Normalize tokens so that each allowed syntax character
	 *  looks like a token for consistent parsing.
	 *
	 *  @param array $tokens
	 *  @return array
	 */
	protected function normalize(array $tokens)
	{
		// Define some constants for consistency.
		// These characters are not "real" tokens.
		defined('T_MINUS')			  ?: define('T_MINUS','-');
		defined('T_BRACKET_OPEN')	   ?: define('T_BRACKET_OPEN','[');
		defined('T_BRACKET_CLOSE')	  ?: define('T_BRACKET_CLOSE',']');
		defined('T_COMMA_SEPARATOR')	?: define('T_COMMA_SEPARATOR',',');
		defined('T_ARRAY_CLOSE')		?: define('T_ARRAY_CLOSE',')');

		// Normalize the token array
		return array_map( function($token) {
			// If the token is a syntax character ($token[0] will be string)
			// then use the token (= $token[0]) as value (= $token[1]) as well.
			return [
				0 => $token[0],
				1 => (is_string($token[0])) ? $token[0] : $token[1]
			];
		}, $tokens);
	}

	/**
	 *  Atomic value parser
	 *
	 *  @param  mixed   $value
	 *  @return mixed
	 */
	protected function parseAtomic($value)
	{
		// If the parameter type is a string
		// then it will be enclosed with quotes
		if (preg_match('/^["\'].*["\']$/', $value))
		{
			// is (already) a string
			return str_replace(['\\','\r','\n'], ["\\","\r","\n"], trim($value, "'\""));
		}

		// Parse integer
		if (preg_match('/^-?(0|[1-9][0-9]*)$/', $value))
		{
			return (int) $value;
		}

		// Parse other sorts of numeric values
		// (floats, scientific notation etc)
		if (is_numeric($value))
		{
			return (float) $value;
		}

		// Parse bool
		if (in_array(strtolower($value), ['true', 'false']))
		{
			return ($value == 'true') ? true : false;
		}

		// Parse null
		if (strtolower($value) === 'null')
		{
			return null;
		}

		// Use string for any remaining values.
		// For example, bitsets are not supported. 0x2,1x2 etc
		return $value;
	}

}


