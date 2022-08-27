<?php
namespace Dately\Traits;


use DateTime;
use DateTimeZone;
use DateTimeImmutable;
use DateInterval;


trait Setters
{
	
	public function year(int $year)
	{
		return $this->setDate(
			$year,
			$this->getMonth(),
			$this->getDay()
		);
	}

	public function month(int $month)
	{
		return $this->setDate(
			$this->getYear(),
			$month,
			$this->getDay()
		);
	}

	public function day(int $day)
	{
		return $this->setDate(
			$this->getYear(),
			$this->getMonth(),
			$day
		);
	}

	public function hour(int $hour)
	{
		return $this->setTime(
			$hour,
			$this->getMinute(),
			$this->getSecond(),
			$this->getMicrosecond()
		);
	}

	public function minute(int $minute)
	{
		return $this->setTime(
			$this->getHour(),
			$minute,
			$this->getSecond(),
			$this->getMicrosecond()
		);
	}

	public function second(int $second)
	{
		return $this->setTime(
			$this->getHour(),
			$this->getMinute(),
			$second,
			$this->getMicrosecond()
		);
	}

	public function microsecond(int $microsecond)
	{
		return $this->setTime(
			$this->getHour(),
			$this->getMinute(),
			$this->getSecond(),
			$microsecond
		);
	}

	public function millisecond(int $millisecond)
	{
		return $this->microsecond($millisecond * 1000);
	}

}

