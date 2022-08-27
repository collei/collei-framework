<?php
namespace Collei\Utils\Parsers;

/**
 *	Parses basic, URL-like DSN variants
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-12-13
 */
class DsnParser
{
	/**
	 *	Parses a basic PDO DSN
	 *
	 *	@param	string	$dsn
	 *	@param	array	&$matches
	 *	@return	bool
	 */
	private static function parsePdo(string $dsn, &$matches)
	{
		$patt = '/((\w+)=([^;]*))/';
		//
		return preg_match_all($patt, $dsn, $matches, PREG_SET_ORDER);
	}

	/**
	 *	Parses a PDO MySQL DSN
	 *
	 *	@param	string	$dsn
	 *	@param	array	&$results
	 *	@param	array	$defaults
	 *	@return	bool
	 */
	public static function parsePdoMysql(string $dsn, array &$results, array $defaults = [])
	{
		$matches = [];
		$results = [];

		if (self::parsePdo($dsn, $matches))
		{
			foreach ($matches as $match)
			{
				$n = trim($match[2]);
				$v = trim($match[3]);

				if ($n == 'pass')
				{
					$n = 'password';
				}
				elseif ($n == 'db' || $n == 'database')
				{
					$n = 'dbname';
				}

				$results[$n] = $v;
			}

			if (!isset($results['host']))
			{
				$results['host'] = $defaults['host'] ?? 'localhost';
			}

			if (!isset($results['port']))
			{
				$results['port'] = $defaults['port'] ?? '3306';
			}

			if (!isset($results['user']))
			{
				$results['user'] = $defaults['user'] ?? 'root';
			}

			// put default, non-in-DSN-present values together
			foreach ($defaults as $dn => $dv)
			{
				if (!array_key_exists($dn, $results))
				{
					$results[$dn] = $dv;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 *	Parses a PDO SqlServer DSN
	 *
	 *	@param	string	$dsn
	 *	@param	array	&$results
	 *	@param	array	$defaults
	 *	@return	bool
	 */
	public static function parsePdoSqlServer(string $dsn, array &$results, array $defaults = [])
	{
		$matches = [];
		$results = [];

		if (self::parsePdo($dsn, $matches))
		{
			foreach ($matches as $match)
			{
				$n = trim($match[2]);
				$v = trim($match[3]);
				$uc_n = strtoupper($n);

				if (in_array($uc_n, ['UID','USER','USERNAME']))
				{
					$n = 'uid';
				}
				elseif (in_array($uc_n, ['PWD','PASS','PASSWORD']))
				{
					$n = 'pwd';
				}
				elseif ($uc_n == 'SERVER')
				{
					$n = 'server';
				}
				elseif (in_array($uc_n, ['DB','DBNAME','DATABASE']))
				{
					$n = 'database';
				}

				$results[$n] = $v;
			}

			if (!isset($results['server']))
			{
				$results['server'] = $defaults['server'] ?? 'localhost';
			}

			if (!isset($results['port']))
			{
				$results['port'] = $defaults['port'] ?? '1433';
			}

			if (!isset($results['uid']))
			{
				$results['uid'] = $defaults['uid'] ?? '';
			}

			if (!isset($results['pwd']))
			{
				$results['pwd'] = $defaults['pwd'] ?? '';
			}

			// put default, non-in-DSN-present values together
			foreach ($defaults as $dn => $dv)
			{
				if (!array_key_exists($dn, $results))
				{
					$results[$dn] = $dv;
				}
			}

			return true;
		}

		return false;
	}

}


