<?php
namespace Collei\Utils\Calendar;

use Collei\Utils\Validation\Validator;
use Collei\Exceptions\ObjectPropertyException;

/**
 *	Embodies date properties
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-09-xx
 */
class Date
{
	/**
	 *	@var array $date_parts
	 */
	private $date_parts = [
		'day' => 0,
		'month' => 0,
		'year' => 0,
		'hour' => 0,
		'minute' => 0,
		'second' => 0,
		'dayOfWeek' => 0,
		'dayOfYear' => 0
	];

	/**
	 *	Validates $anything as a date
	 *
	 *	@static
	 *	@param	mixed	$anything
	 *	@return	bool
	 */
	public static function isDate($anything)
	{
		$str = str_replace('/', '-', $anything);
		return is_numeric(strtotime($str));
	}

	/**
	 *	Converts $anything to a valid date, if possible
	 *
	 *	@static
	 *	@param	mixed	$anything
	 *	@return	void
	 */
	public static function toDate($anything)
	{
		$str = str_replace('/', '-', $anything);
		$num = strtotime($str);
		if (is_numeric($num))
		{
			return $num;
		}
		return 0;
	}

	/**
	 *	Converts $anything to a valid date, if possible
	 *
	 *	@static
	 *	@param	mixed	$anything
	 *	@return	void
	 */
	public static function toDateObject($anything)
	{
		$n = Date::toDate($anything);

		if ($n != 0)
		{
			return new Date($n);
		}
		return null;
	}

	/**
	 *	Fills with value
	 *
	 *	@param	int	$timestamp
	 *	@return	void
	 */
	private function fill(int $timestamp = null)
	{
		$info = [];

		if (!is_null($timestamp))
		{
			$info = getdate($timestamp);
		}
		else
		{
			$info = getdate();
		}

		$date_parts['day'] = $info['mday'];
		$date_parts['month'] = $info['mon'];
		$date_parts['year'] = $info['year'];
		$date_parts['hour'] = $info['hours'];
		$date_parts['minute'] = $info['minutes'];
		$date_parts['second'] = $info['seconds'];
		$date_parts['dayOfWeek'] = $info['wday'];
		$date_parts['dayOfYear'] = $info['yday'];
	}

	/**
	 *	Returns the value as integer time
	 *
	 *	@return	int
	 */
	private function time()
	{
		return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
	}

	/**
	 *	Performs recheck of the value
	 *
	 *	@return	void
	 */
	private function recheck()
	{
		$this->fill($this->time);
	}

	/**
	 *	Instantiates a new date
	 *
	 *	@param	mixed	$value
	 */
	public function __construct($value = null)
	{
		if (!is_null($value))
		{
			if (!Date::isDate($value))
			{
				throw new \InvalidArgumentException('Invalid date format: ' . $value);
			}

			$this->fill((int)$value);
		}
		else
		{
			$this->fill();
		}
	}

	/**
	 *	@property int $dayOfWeek
	 *	@property int $dayOfYear
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->date_parts))
		{
			return $this->date_parts[$name];
		}
	}

	/**
	 *	@property int $day
	 *	@property int $month
	 *	@property int $year
	 *	@property int $hour
	 *	@property int $minute
	 *	@property int $second
	 */
	public function __set($name, int $value)
	{
		$day = $this->date_parts['day'];
		$month = $this->date_parts['month'];
		$year = $this->date_parts['year'];

		if (array_key_exists($name, $this->date_parts))
		{
			if ($name == 'day' && !checkdate($month, $value, $year))
			{
				throw new \InvalidArgumentException('Invalid day for this month/year ('.$month.'/'.$year.'): ' . $value);
			}
			if (($name == 'month' && !Validator::inRange($value, 1, 12))
				|| ($name == 'year' && !Validator::inRange($value, 0, 32767))
				|| ($name == 'hour' && !Validator::inRange($value, 0, 23))
				|| ($name == 'minute' && !Validator::inRange($value, 0, 59))
				|| ($name == 'second' && !Validator::inRange($value, 0, 59)))
			{
				throw new \InvalidArgumentException('Invalid '.$name.': ' . $value);
			}
			if ($name == 'dayOfWeek' || $name == 'dayOfYear')
			{
				throw new ObjectPropertyException('The '.$name.' property is readonly.');
			}

			$this->date_parts[$name] = $value;
			$this->recheck();
		}
		else
		{
			throw new ObjectPropertyException('There is no such '.$name.' property in ' . __CLASS__ . '.');
		}
	}

	/**
	 *	Converts this date to the string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		return date('Y-m-d H:i:s', $this->time());
	}

	/**
	 *	Sets date parameters
	 *
	 *	@param	int	$year
	 *	@param	int	$month
	 *	@param	int	$day
	 *	@return	\Collei\Utils\Calendar\Date
	 */
	public function setDate(int $year, int $month, int $day)
	{
		$this->year = $year;
		$this->month = $month;
		$this->day = $day;
		$this->recheck();
		//
		return $this;
	}

	/**
	 *	Sets time parameters
	 *
	 *	@param	int	$hour
	 *	@param	int	$minute
	 *	@param	int	$second
	 *	@return	\Collei\Utils\Calendar\Date
	 */
	public function setTime(int $hour, int $minute, int $second)
	{
		$this->hour = $hour;
		$this->minute = $minute;
		$this->second = $second;
		$this->recheck();
		//
		return $this;
	}

}


