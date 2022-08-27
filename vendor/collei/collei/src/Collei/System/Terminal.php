<?php
namespace Collei\System;

/**
 *	Encapsulates terminal basic features
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-07-08
 */
class Terminal
{
	/**
	 *	@const int DEFAULT_ROWS = 50
	 *	@const int DEFAULT_COLUMNS = 80
	 */
	public const DEFAULT_ROWS = 50;
	public const DEFAULT_COLUMNS = 80;

	/**
	 *	@var int $rows
	 */
	private static $rows;

	/**
	 *	@var int $columns
	 */
	private static $columns;

	/**
	 *	@var bool $stty
	 */
	private static $stty;

	/**
	 *	Set the $rows and $columns sizes.
	 *
	 *	@param	mixed	$sizes
	 *	@return	void
	 */
	private static function setSizes($sizes)
	{
		if (is_array($sizes) && isset($sizes[0]) && isset($sizes[1])) {
			self::$columns = $sizes[0];
			self::$rows = $sizes[1];
		}
	}

	/**
	 *	Scan data on the console terminal from the system.
	 *
	 *	@return	void
	 */
	private static function scanConstraints()
	{
		$sizes = null;
		//
		if ('\\' == DIRECTORY_SEPARATOR) {
			if ($sizes = self::scanFromEnv('ANSICON')) {
				self::setSizes($sizes);
			} elseif (!self::hasVt100Support() && self::hasStty()) {
				self::setSizes(self::scanFromStty());
			} elseif ($sizes = self::scanFromWin()) {
				self::setSizes($sizes);
			}
		} else {
			self::setSizes(self::scanFromStty());
		}
	}

	/**
	 *	Scan data from the given environment variable
	 *
	 *	@param	string	$env
	 *	@return	array|null
	 */
	private static function scanFromEnv(string $env)
	{
		$found = preg_match(
			'/^(\\d+)x(\\d+)(\\s*\\((\\d+)x(\\d+)\\))?$/i',
			\trim(\getenv($env)),
			$data
		);
		//
		if ($found) {
			return [(int) $data[1], (int) ($data[5] ?? $data[2])];
		}
		//
		return null;
	}

	/**
	 *	Scan data from the Windows console system, if any
	 *
	 *	@return	array|null
	 */
	private static function scanFromWin()
	{
		if ($dosResult = Process::quickRead('mode con')) {
			$found = preg_match(
				'/--------+\\r?\\n.+?(\\d+)\\r?\\n.+?(\\d+)\\r?\\n/i',
				$dosResult,
				$data
			);
			//
			if ($found) {
				return [(int) $data[2], (int) $data[1]];
			}
		}
		//
		return null;
	}

	/**
	 *	Scan data from the tty device through an Unix-like command
	 *
	 *	@return	array|null
	 */
	private static function scanFromStty()
	{
		if ($sttyOut = Process::quickRead('stty -a | grep columns')) {
			if (preg_match('/rows.(\\d+);.columns.(\\d+);/i', $sttyOut, $data)) {
				// [w,h] from 'rows h; columns w;'
				return [(int) $data[2], (int) $data[1]];
			} elseif (preg_match('/;.(\\d+).rows;.(\\d+).columns/i', $sttyOut, $data)) {
				// [w,h] from '; h rows; w columns'
				return [(int) $data[2], (int) $data[1]];
			}
		}
		//
		return null;
	}

	/**
	 *	Checks for Vt100 support presence
	 *
	 *	@return	bool
	 */
	private static function hasVt100Support()
	{
		if (!function_exists('sapi_windows_vt100_support')) {
			return false;
		}
		//
		return sapi_windows_vt100_support(STDOUT);
	}

	/**
	 *	Checks for stty device presence
	 *
	 *	@return	bool
	 */
	private static function hasStty()
	{
		if (null !== self::$stty) {
			return self::$stty;
		}
		//
		if (!\function_exists('exec')) {
			return false;
		}
		//
		exec('stty 2>&1', $out, $exitCode);
		//
		return self::$stty = (0 === $exitCode);
	}

	/**
	 *	Returns terminal's row count
	 *
	 *	@return	int
	 */
	public function getRowCount()
	{
		if (false !== ($rows = getenv('LINES'))) {
			return (int) $rows;
		}
		//
		if (null === self::$rows) {
			self::scanConstraints();
		}
		//
		return self::$rows ?: self::DEFAULT_ROWS;
	}

	/**
	 *	Returns terminal's column count
	 *
	 *	@return	int
	 */
	public function getColumnCount()
	{
		if (false !== ($columns = getenv('COLUMNS'))) {
			return (int) $columns;
		}
		//
		if (null === self::$columns) {
			self::scanConstraints();
		}
		//
		return self::$columns ?: self::DEFAULT_COLUMNS;
	}

}

	

