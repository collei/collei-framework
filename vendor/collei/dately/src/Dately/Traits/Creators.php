<?php
namespace Dately\Traits;


use DateTime;
use DateTimeZone;
use DateTimeImmutable;
use DateInterval;


trait Creators
{
	
	/*--------------------------*
	 *	static helpers
	 *--------------------------*/

	public static function now($timezone = null)
	{
		return new static('now', $timezone);
	}

	public static function yesterday($timezone = null)
	{
		return static::today($timezone)->modify('-1 day');
	}

	public static function today($timezone = null)
	{
		return new static('today', static::filterTimeZone($timezone));
	}

	public static function tomorrow($timezone = null)
	{
		return static::today($timezone)->modify('+1 day');
	}

	public static function create(
		int $year = null, int $month = null, int $day = null, 
		int $hour = null, int $minute = null, int $second = null, $tz = null
	)
	{
		$dt = new static();

		return (new static('now', $tz))->setDate(
				$year ?? $dt->format('Y'),
				$month ?? $dt->format('m'),
				$day ?? $dt->format('d')
			)->setTime(
				$hour ?? $dt->format('H'),
				$minute ?? $dt->format('i'),
				$second ?? $dt->format('s')
			);
	}

	public static function createFromDate(
		int $year = null, int $month = null, int $day = null, $tz = null
	)
	{
		$dt = new static();

		return (new static('now', $tz))->setDate(
				$year ?? $dt->format('Y'),
				$month ?? $dt->format('m'),
				$day ?? $dt->format('d')
			);
	}

	public static function createFromDateString(string $dateString, $tz = null)
	{
		return new static($dateString, $tz);
	}

	public static function createFromMidnightDate(
		int $year = null, int $month = null, int $day = null, $tz = null
	)
	{
		$dt = new static();

		return static::today($tz)->setDate(
				$year ?? $dt->format('Y'),
				$month ?? $dt->format('m'),
				$day ?? $dt->format('d')
			);
	}

	public static function createFromMidnightDateString(string $dateString, $tz = null)
	{
		return (new static($dateString, $tz))->setTime(0,0,0);
	}

	public static function createFromTime(
		int $hour = null, int $minute = null, int $second = null, $tz = null
	)
	{
		$dt = new static();

		return (new static('now', $tz))->setTime(
				$hour ?? $dt->format('H'),
				$minute ?? $dt->format('i'),
				$second ?? $dt->format('s')
			);
	}

	public static function createFromTimeString(string $timeString, $tz = null)
	{
		return new static($timeString, $tz);
	}

	public static function parse($time = 'now', $tz = null)
	{
		if ($time instanceof DateTime || $time instanceof DateTimeImmutable)
		{
			return (new static(null, $tz))->year($time->format('Y'))
						->month($time->format('m'))
						->day($time->format('d'))
						->hour($time->format('H'))
						->minute($time->format('i'))
						->second($time->format('s'));
		}

		if (is_string($time))
		{
			return (new static($time, $tz));
		}

		return new static();
	}

}

