<?php
namespace Collei\Utils;

use RangeException;

/**
 *	Reunites string helper functions
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
abstract class Str
{
	/**
	 * Generate a random string, using a cryptographically secure 
	 * pseudorandom number generator (random_int)
	 *
	 * This function uses type hints now (PHP 7+ only), but it was originally
	 * written for PHP 5 as well.
	 * 
	 * For PHP 7, random_int is a PHP core function
	 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
	 * 
	 *	@author	Scott Arciszewski <https://stackoverflow.com/users/2224584/scott-arciszewski>
	 *	@since	2015-06-29	from https://stackoverflow.com/questions/4356289/php-random-string-generator/31107425#31107425 (viewed 2021-11-02)
	 *
	 *
	 *	@param	int		$length		How many characters do we want?
	 *	@param	string	$keyspace	A string of all possible characters to select from
	 *	@return string
	 */
	public static function random(
		int $length = 64,
		string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
	): string {
		if ($length < 1) {
			throw new RangeException("Length must be a positive integer");
		}
		//
		$pieces = [];
		$max = \mb_strlen($keyspace, '8bit') - 1;
		//$max = \strlen($keyspace) - 1;
		//
		for ($i = 0; $i < $length; ++$i) {
			$pieces[] = $keyspace[\random_int(0, $max)];
		}
		//
		return \implode('', $pieces);
	}

	/**
	 *	Tells if thisComplexName is in camelCase
	 *
	 *	@param	string	$camel
	 *	@return	bool
	 */
	public static function isCamel(string $camel)
	{
		return \preg_match('/^((\G(?!^)|\b[a-zA-Z][a-z\d]*)([A-Z][a-z\d]*)*|[a-z][a-z\d]*)$/', $camel) === 1;
	}

	/**
	 *	Converts this_complex_name to thisComplexName
	 *
	 *	@param	string	$snake
	 *	@return	string
	 */
	public static function toCamel(string $snake)
	{
		return \lcfirst(\implode('', \array_map(function($p){ return \ucfirst($p); }, \explode('_',$snake))));
	}

	/**
	 *	Tells if this_complex_name is in snake format
	 *
	 *	@param	string	$snake
	 *	@return	bool
	 */
	public static function isSnake(string $snake)
	{
		return \preg_match('/^[a-z][a-z\d]*(_[a-z][a-z\d]*)*$/', $snake) === 1;
	}

	/**
	 *	Converts thisComplexName to this_complex_name
	 *
	 *	@param	string	$camel
	 *	@return	string
	 */
	public static function toSnake(string $camel)
	{
		return \strtolower(\preg_replace('/(\G(?!^)|\b[a-zA-Z][a-z]*)([A-Z][a-z]*|\d+)/', '$1_$2', $camel));
	}

	/**
	 *	Returns the unclosed version of the given $str if it has parenthesis,
	 *	curly brackets etc.
	 *	supported types: () [] {} <> «»
	 *
	 *	@param	string	$str		the string
	 *	@param	string	...$with	which types to consider (empty = all)
	 *	@return	string
	 */
	public static function isClosed(string $str, string ...$with)
	{
		if (empty($str)) {
			return false;
		}
		//
		$closes = [
			'pairs' => [
				['(',')'],['[',']'],['{','}'],['«','»'],['<','>']
			], 'types' => [
				'()','[]','{}','«»','<>'
			]
		];
		//
		$str = \trim($str);
		$with = empty($with) ? $closes['types'] : $with;
		//
		$q0 = \substr($str, 0, 1);
		$qf = \substr($str, -1, 1);
		//
		foreach ($closes['pairs'] as $n => $pair) {
			if (\in_array($q0.$qf, $with)) {
				if (\in_array([$q0,$qf], $closes['pairs'])) {
					return true;
				}
			}
		}
		//
		return false;
	}

	/**
	 *	Tells if the string starts with $prefix
	 *
	 *	@param	string	$str
	 *	@param	string	$prefix
	 *	@return	bool
	 */
	public static function startsWith(string $str, string $prefix)
	{
		return \str_starts_with($str, $prefix);
	}

	/**
	 *	Tells if the string ends with $suffix
	 *
	 *	@param	string	$str
	 *	@param	string	$suffix
	 *	@return	bool
	 */
	public static function endsWith(string $str, string $suffix)
	{
		return \str_ends_with($str, $suffix);
	}

	/**
	 *	Tells if something is inside the string
	 *
	 *	@param	string	$needle
	 *	@param	string	$haystack
	 *	@return	bool
	 */
	public static function has(string $needle, string $haystack)
	{
		return \strpos($haystack, $needle) !== FALSE;
	}

	/**
	 *	Splits a string using the delimiter as knife
	 *
	 *	@param	string	$knife
	 *	@param	string	$beefsteak
	 *	@return	array
	 */
	public static function explode(string $knife, string $beefsteak)
	{
		return \explode($knife, $beefsteak);
	}

	/**
	 *	String replacement
	 *
	 *	@param	string|array	$search
	 *	@param	string|array	$replacement
	 *	@param	string			$subject
	 *	@return	string
	 */
	public static function replace($search, $replacement, string $subject)
	{
		if (!\is_array($search) && \is_array($replacement)) {
			$replacement = \array_shift($replacement);
		}
		//
		if (!\is_array($replacement) && !\is_string($replacement)) {
			$replacement = '' . $replacement . '';
		}
		//
		return \str_replace($search, $replacement, $subject);
	}

	/**
	 *	Returns the unquoted version of the given $str if it has quotes
	 *
	 *	@param	string	$str	the string
	 *	@return	string
	 */
	public static function unquote(string $str)
	{
		if (empty($str)) {
			return $str;
		}
		//
		$str = \trim($str);
		$q0 = \substr($str, 0, 1);
		$qf = \substr($str, -1, 1);
		//
		if (\in_array($q0, ['"',"'"]) && ($q0 == $qf)) {
			return \substr($str, 1, -1);
		}
		//
		return $str;
	}

	/**
	 *	Returns the unclosed version of the given $str if it has parenthesis,
	 *	curly brackets etc.
	 *
	 *	@param	string	$str	the string
	 *	@return	string
	 */
	public static function unclose(string $str)
	{
		if (empty($str)) {
			return $str;
		}
		//
		$str = trim($str);
		$closeds = [
			['(',')'],
			['[',']'],
			['{','}'],
			['«','»'],
			['<','>'],
		];
		//
		$q0 = \substr($str, 0, 1);
		$qf = \substr($str, -1, 1);
		//
		if (\in_array([$q0,$qf], $closeds) && ($q0 == $qf))
		{
			return \substr($str, 1, -1);
		}
		//
		return $str;
	}

	/**
	 *	Returns the common string that is both the suffix of $front
	 *	and the prefix of $rear. If none, empty string is returned.
	 *
	 *	@author	Almir J.	<alarido.su@gmail.com>
	 *	@since	2021-11-16		
	 *	@param	string	$front
	 *	@param	string	$rear
	 *	@return	string
	 */
	public static function collision(string $front, string $rear)
	{
		// initialize the result
		$collided = '';
		// split both into array of char
		$ca_front = \str_split($front);
		$ca_rear = \str_split($rear);
		// define the $front boundary and current index
		$len_front = \count($ca_front);
		$f = 0;
		// helps stop looping at appropriate moment
		$found = false;		
		//
		while ($f < $len_front) {
			// the character
			$ch_fo = $ca_front[$f];
			// discards any partial result if the end of $front
			// was not yet reached
			if (!$found) {
				$collided = '';
			}
			// second pointer to the $front current char
			$delta = 1;
			//
			foreach ($ca_rear as $r => $ch_re) {
				//	if we reached the end of $front, it's time to stop 
				if (($f + $delta) >= $len_front) {
					$found = true;
					break;
				}
				// get the char to compare
				$ch_fo = $ca_front[$f + $delta];
				// updates the collided
				$collided .= $ch_re;
				// stop looping if both differ.
				// It makes discarding partial result
				// and try again at next $front char
				if ($ch_fo != $ch_re) {
					break;
				}
				// increase the delta
				++$delta;
			}
			// stop looping if we reached the end of $front
			if ($found) {
				break;
			}
			//
			++$f;
		}
		// if empty, it means no collision was found.
		return $collided;
	}

	/**
	 *	Returns whether is there a collision, i.e., a common string that is both
	 *	the suffix of $front and the prefix of $rear.
	 *
	 *	@author	Almir J.	<alarido.su@gmail.com>
	 *	@since	2021-11-16		
	 *	@param	string	$front
	 *	@param	string	$rear
	 *	@return	bool	true if a the collision exists, false otherwise		
	 */
	public static function collided(string $front, string $rear)
	{
		return self::collision($front, $rear) != '';
	}

	/**
	 *	Join two strings - $front and $rear - ignoring the collision,
	 *	i.e., it does not get repeated in the middle of the resulting string
	 *
	 *	@author	Almir J.	<alarido.su@gmail.com>
	 *	@since	2021-11-16		
	 *	@param	string	$front
	 *	@param	string	$rear
	 *	@return	bool	true if a the collision exists, false otherwise		
	 */
	public static function collapse(string $front, string $rear)
	{
		$collision = self::collision($front, $rear);
		//
		if ($collision == '') {
			return $front . $rear;
		}
		//
		return \substr($front, 0, \strlen($front) - \strlen($collision)) . $rear;
	}

	/**
	 *	Tokenize lines by spaces, except that tokens wrapped by "..." or '...'
	 *	will remain a single token, no matter how may spaces may exist inside
	 *
	 *	@param	string	$str	the string to be tokenized	
	 *	@return	array
	 */
	public static function tokenize(string $str)
	{
		$chars = \str_split($str);
		$tokens = [];
		$first_quote = '';
		$last = '';
		$current = '';
		// mini-function for reuse
		$break_if_nempty = function(&$items, &$item) {
			if (!empty($item)) {
				$items[] = $item;
				$item = '';
			}
		};
		//
		foreach ($chars as $ch) {
			if ($ch == ' ' || $ch == "\t") {
				if ($first_quote == '') {
					$break_if_nempty($tokens, $current);
				} else {
					$current .= $ch;
				}
			} elseif ($ch == '"' || $ch == "'") {
				if ($first_quote == $ch) {
					if ($last == '\\') {
						$current = \substr($current,0,-1) . $ch;
					} else {
						$first_quote = '';
						$break_if_nempty($tokens, $current);
					}
				} elseif ($first_quote == '') {
					$first_quote = $ch;
					$break_if_nempty($tokens, $current);
				} else {
					$current .= $ch;
				}
			} else {
				$current .= $ch;
			}
			// keep track of last char
			$last = $ch;
		}
		//
		$tokens[] = $current;
		//
		return $tokens;
	}

	/**
	 *	splits $str in two through $chars and removes just the first part   
	 *
	 *	@param	string	$str
	 *	@param	string	$chars
	 *	@return string	
	 */
	public static function stripAfter(string $str, string $chars)
	{
		$parts = \explode($chars, $str, 2);
		//
		return $parts[0];
	}

	/**
	 *	checks if a string is in the list
	 *
	 *	@param	string	$str
	 *	@param	array	$strings
	 *	@param	bool	$ignoreCase	true to case insensitive, false otherwise
	 *	@return	bool
	 */
	public static function in(string $str, array $strings, bool $ignoreCase = true)
	{
		if ($ignoreCase) {
			foreach ($strings as $string) {
				if (\strcasecmp($str, $string) == 0) {
					return true;
				}
			}
			return false;
		}
		//
		return \in_array($str, $strings);
	}

	/**
	 *	transform a string with line separators into an array with such lines
	 *
	 *	@param	string	$str
	 *	@return	array
	 */
	public static function linesToArray(string $str)
	{
		$string = \str_replace(["\r\n","\n","\r"],"\x01",$str);
		//
		return \explode("\x01", $string);
	}

	private static $DIACRITICS_MAP = [
		'A' 	=>	['Á','À','Â','Ä','Ã','Ā','Ă','Ą'],
		'a' 	=>	['á','à','â','ä','ã','ā','ă','ą'],
		'C' 	=>	['Ç','Ć','Ĉ','Ċ','Č'],
		'c' 	=>	['ç','ć','ĉ','ċ','č'],
		'D' 	=>	['Ď','Đ'],
		'd' 	=>	['ď','đ'],
		'E' 	=>	['É','È','Ê','Ë','Ē','Ĕ','Ė','Ę','Ě'],
		'e' 	=>	['é','è','ê','ë','ē','ĕ','ė','ę','ě'],
		'G' 	=>	['Ĝ','Ğ','Ġ','Ģ'],
		'g' 	=>	['ĝ','ğ','ġ','ģ'],
		'H' 	=>	['Ĥ','Ħ'],
		'h' 	=>	['ĥ','ħ'],
		'I' 	=>	['Í','Ì','Î','Ï','Ĩ','Ī','Ĭ','Į','İ'],
		'i' 	=>	['í','ì','î','ï','ĩ','ī','ĭ','į','ı'],
		'IJ' 	=>	['Ĳ'],
		'ij' 	=>	['ĳ'],
		'J' 	=>	['Ĵ'],
		'j' 	=>	['ĵ'],
		'K' 	=>	['Ķ'],
		'k' 	=>	['ķ','ĸ'],
		'L' 	=>	['Ĺ','Ļ','Ľ','Ŀ','Ł'],
		'l' 	=>	['ĺ','ļ','ľ','ŀ','ł'],
		'N' 	=>	['Ñ','Ń','Ņ','Ň'],
		'n' 	=>	['ñ','ń','ņ','ň','ŉ'],
		'NJ' 	=>	['Ŋ'],
		'nj' 	=>	['ŋ'],
		'O' 	=>	['Ó','Ò','Ô','Ö','Õ','Ō','Ŏ','Ő'],
		'o' 	=>	['ó','ò','ô','ö','õ','ō','ŏ','ő'],
		'OE' 	=>	['Œ'],
		'oe' 	=>	['œ'],
		'R' 	=>	['Ŕ','Ŗ','Ř'],
		'r' 	=>	['ŕ','ŗ','ř'],
		'S' 	=>	['Ś','Ŝ','Ş','Š'],
		's' 	=>	['ś','ŝ','ş','š'],
		'T' 	=>	['Ţ','Ť','Ŧ'],
		't' 	=>	['ţ','ť','ŧ'],
		'U' 	=>	['Ú','Ù','Û','Ü','Ũ','Ū','Ŭ','Ů','Ű','Ų'],
		'u' 	=>	['ú','ù','û','ü','ũ','ū','ŭ','ů','ű','ų'],
		'W' 	=>	['Ŵ'],
		'w' 	=>	['ŵ'],
		'Y' 	=>	['Ŷ','Ÿ'],
		'y' 	=>	['ŷ','ÿ'],
		'Z' 	=>	['Ź','Ż','Ž'],
		'z' 	=>	['ź','ż','ž'],
	];

	/**
	 *	removes diacritics from string
	 *
	 *	@param	string	$input
	 *	@return	string
	 */
	public static function cleanDiacritics(string $input)
	{
		$str = $input;
		//
		foreach (self::$DIACRITICS_MAP as $letr => $base) {
			$str = \str_replace($base, $letr, $str);
		}
		//
		return $str;
	}

	/**
	 *	insert line numbers at the start of each line in the given string
	 *
	 *	@param	string	$input
	 *	@return	string
	 */
	public static function withLineNumbers(string $input)
	{
		$lines = \explode("\n", \str_replace(["\r\n","\r"], "\n", $input));
		$formed = [];
		//
		foreach ($lines as $i => $line) {
			$formed[] = ($i+1) . "\t" . $line;
		}
		//
		return \implode("\r\n", $formed);
	}

	/**
	 *	Returns the string with the prefix removed
	 *
	 *	@param	string	$input
	 *	@param	string	$prefix
	 *	@return	string
	 */
	public static function trimPrefix(string $str, string $prefix)
	{
		while (Str::startsWith($str, $prefix)) {
			$str = \substr($str, \strlen($prefix));
		}
		//
		return $str;
	}

	/**
	 *	Returns the string with the suffix removed
	 *
	 *	@param	string	$input
	 *	@param	string	$suffix
	 *	@return	string
	 */
	public static function trimSuffix(string $str, string $suffix)
	{
		while (Str::endsWith($str, $suffix)) {
			$str = \substr($str, 0, \strlen($str) - \strlen($suffix));
		}
		//
		return $str;
	}

	/**
	 *	Returns the string with both prefix and suffix removed
	 *
	 *	@param	string	$input
	 *	@param	string	$prefix
	 *	@param	string	$suffix
	 *	@return	string
	 */
	public static function trimBoth(string $str, string $prefix, string $suffix)
	{
		return Str::trimSuffix(
			Str::trimPrefix($str, $prefix), $suffix
		);
	}

	/**
	 *	Returns the nth named variable argument in the string, path, etc
	 *
	 *		$str = "I want a {type} {flavor} {dessert}.";
	 *		$name = Str::getNamedArg($str, 1, '{', '}');
	 *			-> type
	 *		$name = Str::getNamedArg($str, 2, '{', '}');
	 *			-> flavor
	 *
	 *		$str = "I want a %type% {flavor} %dessert%.";
	 *		$name = Str::getNamedArg($str, 1, '%');
	 *			-> type
	 *		$name = Str::getNamedArg($str, 2, '%');
	 *			-> dessert
	 *
	 *	@param	string	$str
	 *	@param	int		$index
	 *	@param	mixed	$begin
	 *	@param	mixed	$end = null
	 *	@return	string
	 */
	public static function getNamedArg(
		string $str, int $index, $begin, $end = null
	) {
		if (empty($str) || empty($begin) || ($index < 1)) {
			return '';
		}

		$end = $end ?? $begin;
		$offset = 0;
		$name = '';

		while ($index >= 1) {
			if (($pos = \strpos($str, $begin, $offset)) !== false) {
				$offset = $pos + 1;
				//
				if (($pos2 = \strpos($str, $end, $pos + 1)) !== false) {
					$offset = $pos2 + 1;
					//
					$strBegin = $pos + \strlen($begin);
					$strEnd = $pos2;
					//
					$name = \substr($str, $strBegin, $strEnd - $strBegin);
				}
			}
			//
			--$index;
		}

		return $name;
	}

	/**
	 *	Counts the number of lines up to the specified $limit, where
	 *	$limit is the last character index of the string.
	 *	If $limit is omitted, the whole $text is considered.
	 *
	 *	@param	string	$text
	 *	@param	int		$limit
	 *	@return	int
	 */
	public static function countLines(string $text, int $limit = null)
	{
		if (!\is_null($limit) && ($limit > 0)) {
			return 1 + \substr_count(
				\substr($text, 0, $limit), PHP_EOL
			);
		}

		return 1 + \substr_count($text, PHP_EOL);
	}

	/**
	 *	Parses command-line argument lines into pieces.
	 *	Supports escaping the delimiter quote with a backslash
	 *	inside a quoted argument (e.g., " \" " or ' \' '),
	 *	depending on which quote is used.
	 *
	 *	@param	string	$thing
	 *	@return	array
	 */
	public static function parseArguments(string $thing)
	{
		$encloser = '';
		$piece = '';
		$last = '';
		$escaper = '\\';
		$pieces = [];
		//
		$chars = \mb_str_split($thing);
		//
		foreach ($chars as $ch) {
			if (($ch === "\"") || ($ch === '\'')) {
				if (empty($encloser)) {
					$encloser = $ch;
				} else {
					if ($ch === $encloser) {
						if ($last == $escaper) {
							$piece = \substr($piece, 0, -1) . $ch;
						} else {
							$encloser = '';
						}
					} else {
						$piece .= $ch;
					}
				}
			} elseif (\trim($ch) === '') {
				if (empty($encloser)) {
					$pieces[] = $piece;
					$piece = '';
				} else {
					$piece .= $ch;
				}
			} else {
				$piece .= $ch;
			}
			//
			$last = $ch;
		}
		//
		$pieces[] = $piece;
		//
		return $pieces;
	}

	public const PADDING_ALIGN_LEFT = -1;
	public const PADDING_ALIGN_CENTER = 0;
	public const PADDING_ALIGN_RIGHT = 1;

	/**
	 *	Returns a padded version of the string.
	 *
	 *	@param	string	$str
	 *	@param	int	$size
	 *	@param	int	$alignment = -1		(-1:left, 0:center, 1:right)
	 *	@param	string	$padWith = ' '	(uses only the first char of $padWith)
	 *	@return	string
	 */
	public static function pad(
		string $str,
		int $size,
		int $alignment = -1,
		string $padWith = ' '
	) {
		$len = \strlen($str);
		$size = \abs($size);
		$padWith = \substr($padWith, 0, 1);
		//
		if ($len == $size) {
			return $str;
		}
		//
		if ($len > $size) {
			if ($alignment > 0) {
				// right
				return \substr($str, -$size);
			} elseif ($alignment < 0) {
				// left
				return \substr($str, 0, $size);
			} else {
				// center
				$half = (int)(($len - $size) / 2);
				return \substr(
					\substr($str, $half, -$half), 0, $size
				);
			}
		}
		//
		if ($alignment > 0) {
			// right alignment
			while (\strlen($str) <= $size) {
				$str = $padWith . $str;
			}
		} elseif ($alignment < 0) {
			// left alignment
			while (\strlen($str) < $size) {
				$str .= $padWith;
			}
		} else {
			// center alignment
			while (\strlen($str) < $size) {
				$str = $padWith . $str . $padWith;
			}
		}
		//
		return \substr($str, 0, $size);
	}

	/**
	 *	Returns a string formed by the repeated char.
	 *
	 *	@param	string	$char
	 *	@param	int	$size
	 */
	public static function repeat(string $char, int $size)
	{
		return \str_repeat($char, $size);
	}


}


