<?php
namespace Collei\App;

use Collei\Utils\Files\TextFile;

/**
 *	Encapsulates logging tasks and capabilities
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-06-xx
 */
class Logger
{
	/**
	 *	constants
	 */
	public const PLAT_ERROR_FATAL = 0;
	public const PLAT_ERROR_WARNING = 2;
	public const PLAT_ERROR_ERROR = 1;
	public const PLAT_ERROR_NOTICE = 4;
	public const PLAT_ERROR_CUSTOM = 1024;

	public const PLAT_SEVERITY_TEXT = [
		Logger::PLAT_ERROR_FATAL => 'Fatal Error',
		Logger::PLAT_ERROR_WARNING => 'Warning',
		Logger::PLAT_ERROR_ERROR => 'Error',
		Logger::PLAT_ERROR_NOTICE => 'Notice',
		Logger::PLAT_ERROR_CUSTOM => 'Custom Error',
	];

	/**
	 *	@static @var array $logger_registry
	 */
	private static $logger_registry = array();

	/**
	 *	Records the generated log in a file
	 *
	 *	@param	string	$filename
	 *	@return	void
	 */
	private static function saveLogsTo(string $filename)
	{
		$sections = self::$logger_registry;
		$file = new TextFile();
		//
		foreach ($sections as $section_name => $logs)
		{
			foreach ($logs as $log)
			{
				$time = date_format($log['timestamp'], 'Y-m-d H:i:s.u');
				$error = $log['error'];
				$descr = $log['description'];
				$sevrt = $log['severity'];
				//
				$line = "[$time] [$section_name] [$sevrt] [$error] $descr ";
				//
				$file->writeLine($line);
			}
		}
		//
		$file->appendTo($filename);
	}

	/**
	 *	Register the log message with the selected severity
	 *
	 *	@param	mixed	$sector
	 *	@param	mixed	$error
	 *	@param	mixed	$description
	 *	@param	mixed	$severity
	 *	@return	void
	 */
	public static function log($sector, $error, $description, $severity = Logger::PLAT_ERROR_ERROR)
	{
		if (!array_key_exists($sector, self::$logger_registry))
		{
			self::$logger_registry[$sector] = array();
		}
		//
		$severity = Logger::PLAT_SEVERITY_TEXT[$severity]
			?? Logger::PLAT_SEVERITY_TEXT[Logger::PLAT_ERROR_CUSTOM];
		//
		self::$logger_registry[$sector][] = [
			'error' => $error,
			'description' => $description,
			'severity' => $severity,
			'timestamp' => date_create()
		];
	}

	/**
	 *	Returns an array with all logged events
	 *
	 *	@return	array
	 */
	public static function getAll()
	{
		$logs = array();
		//
		foreach (self::$logger_registry as $sector => $items)
		{
			foreach ($items as $item)
			{
				$item['section'] = $sector;
				$logs[] = $item;
			}
		}
		//
		return $logs;
	}

	/**
	 *	Records the generated log in a timely-manner-named file
	 *
	 *	@param	string	$prefix
	 *	@return	void
	 */
	public static function save(string $prefix = null)
	{
		if (count(self::$logger_registry) > 0)
		{
			$filename = PLAT_LOGS_GROUND . DIRECTORY_SEPARATOR 
				. '.'
				. ( (!is_null($prefix) && ($prefix!='')) ? $prefix . '-' : '')
				. date('Ymd') . '.log';
			//
			self::saveLogsTo($filename);
		}
	}
		
}

