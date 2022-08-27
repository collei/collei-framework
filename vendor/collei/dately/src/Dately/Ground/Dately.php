<?php 
namespace Dately\Ground;

use Dately\Traits\Date;
use Dately\Traits\Changes;
use Dately\Traits\Compares;
use Dately\Traits\Creators;
use Dately\Traits\Setters;
use Collei\Exceptions\UnknownPropertyException;
use Collei\Exceptions\UnknownMethodException;
use InvalidArgumentException;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateInterval;
use DateTimeZone;
use date_default_timezone_get;

class Dately extends DateTime
{
	use Creators, Changes, Compares, Setters;

	protected static $hereNow = null;

	protected static $hereTz = null;

	protected static function convertibleTime($anything)
	{
		if ($anything instanceof DateTimeInterface)
		{
			return $anything->copy();
		}

		if (is_string($anything))
		{
			$anytime = null;

			try
			{
				$anytime = new static($anything);
			}
			catch (Exception $ex)
			{
				return false;
			}

			return $anytime;
		}

		return false;
	}

	protected static function hasTime($anything)
	{
		if ($ct = static::convertibleTime($anything))
		{
			return $ct->format('H:i:s') !== '00:00:00';
		}

		return false;
	}

	protected static function removeTime($anything)
	{
		if ($ct = static::convertibleTime($anything))
		{
			return $ct->setTime(0, 0, 0);
		}

		return $anything;
	}


	protected static function getIntervalSeconds($first, $last, bool $ignoreTime = false)
	{
		if (is_null($first) || is_null($last))
		{
			return 0;
		}

		if ($fi = static::convertibleTime($first))
		{
			if ($la = static::convertibleTime($last))
			{
				if ($ignoreTime)
				{
					$fi->setTime(0, 0, 0);
					$la->setTime(0, 0, 0);
				}

				return $la->getTimestamp() - $fi->getTimestamp();
			}
		}

		return 0;
	}



	protected static function filterTimeZone($tz = null)
	{
		if (is_string($tz) || is_int($tz))
		{
			try {
				$tz = new DateTimeZone($tz);
			} catch (Exception $ex) {
				$tz = new DateTimeZone(@date_default_timezone_get());
			}
		}
		elseif (!($tz instanceof DateTimeZone))
		{
			$tz = new DateTimeZone(@date_default_timezone_get());
		}

		return $tz;
	}

	/*--------------------------*
	 *	public stake
	 *--------------------------*/

	public function __construct(string $time = 'now', $timezone = null)
	{
		parent::__construct($time);

		$this->setTimezone(static::filterTimeZone($timezone));
	}

	/*--------------------------*
	 *	live members
	 *--------------------------*/

	public function __toString()
	{
		return $this->format('Y-m-d H:i:s P');
	}

	private function getIntIfNumeric(&$interval)
	{
		if (is_numeric($interval))
		{
			$interval = (int)(double)$interval;
			return true;
		}
		//
		return false;
	}

	public function add($interval, string $period = 'day')
	{
		if ($interval instanceof DateInterval)
		{
			return parent::add($interval, $period);
		}

		if ($this->getIntIfNumeric($interval))
		{
			$mounted = ($interval > 0 ? '+' : '-') . abs($interval) . ' ' . $period;

			return $this->modify($mounted);
		}

		return false;
	}

	public function sub($interval, string $period = 'day')
	{
		if ($interval instanceof DateInterval)
		{
			return parent::sub($interval, $period);
		}

		if ($this->getIntIfNumeric($interval))
		{
			$mounted = ($interval > 0 ? '-' : '+') . abs($interval) . ' ' . $period;

			return $this->modify($mounted);
		}

		return false;
	}

	public function copy()
	{
		return clone $this;
	}

	/*--------------------------*
	 *	getters
	 *--------------------------*/

	public function __get(string $name)
	{
		static $valid = [
			'year', 'month', 'day',
			'hour', 'minute', 'second', 'millisecond', 'microsecond'
		];

		if (!in_array($name, $valid))
		{
			throw new UnknownPropertyException('There is no such property: ' . $name);
		}

		switch ($name) {
			case 'year':
				return (int)$this->format('Y');
			case 'month':
				return (int)$this->format('m');
			case 'day':
				return (int)$this->format('d');
			case 'hour':
				return (int)$this->format('H');
			case 'minute':
				return (int)$this->format('i');
			case 'second':
				return (int)$this->format('s');
			case 'millisecond':
				return (int)$this->format('u');
			case 'microsecond':
				return (int)$this->format('v');
		}

	}

	/*--------------------------*
	 *	setters
	 *--------------------------*/

	public function __set(string $name, $value)
	{
		static $valid = [
			'year', 'month', 'day',
			'hour', 'minute', 'second', 'millisecond', 'microsecond'
		];

		if (!in_array($name, $valid))
		{
			throw new UnknownMethodException('There is no such method: ' . $name);
		}

		if (!is_numeric($value))
		{
			throw new InvalidArgumentException('Method ' . $name . ' requires a numeric Value.');
		}

		$val = (int)(double)$value;

		switch ($name)
		{
			case 'year':
				return $this->setDate($val, $this->month, $this->day);
			case 'month':
				return $this->setDate($this->year, $val, $this->day);
			case 'day':
				return $this->setDate($this->year, $this->month, $val);
			case 'hour':
				return $this->setTime($val, $this->minute, $this->second, $this->microsecond);
			case 'minute':
				return $this->setTime($this->hour, $val, $this->second, $this->microsecond);
			case 'second':
				return $this->setTime($this->hour, $this->minute, $val, $this->microsecond);
			case 'millisecond':
				return $this->setTime($this->hour, $this->minute, $this->second, $val * 1000);
			case 'microsecond':
				return $this->setTime($this->hour, $this->minute, $this->second, $val);
		}

		return $this;
	}



}



