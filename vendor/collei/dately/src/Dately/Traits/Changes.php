<?php
namespace Dately\Traits;

use Dately\Ground;
use DateTime;


trait Changes
{

	/*--------------------------*
	 *	add~ shorthands
	 *--------------------------*/

	public function addSecond(int $interval = 1)
	{
		return $this->add($interval, 'second');
	}

	public function addSeconds(int $interval)
	{
		return $this->add($interval, 'second');
	}

	public function addMinute(int $interval = 1)
	{
		return $this->add($interval * 1, 'minute');
	}

	public function addMinutes(int $interval)
	{
		return $this->add($interval, 'minute');
	}

	public function addHour(int $interval = 1)
	{
		return $this->add($interval * 1, 'hour');
	}

	public function addHours(int $interval)
	{
		return $this->add($interval, 'hour');
	}

	public function addDay(int $interval = 1)
	{
		return $this->add($interval * 1, 'day');
	}

	public function addDays(int $interval)
	{
		return $this->add($interval, 'day');
	}

	public function addMonth(int $interval = 1)
	{
		return $this->add($interval * 1, 'month');
	}

	public function addMonths(int $interval)
	{
		return $this->add($interval, 'month');
	}

	public function addQuarter(int $interval = 1)
	{
		return $this->add($interval * 3, 'month');
	}

	public function addQuarters(int $interval)
	{
		return $this->add($interval * 3, 'month');
	}

	public function addYears(int $interval)
	{
		return $this->add($interval, 'year');
	}

	public function addDecade(int $interval = 1)
	{
		return $this->add($interval * 10, 'year');
	}

	public function addDecades(int $interval)
	{
		return $this->add($interval * 10, 'year');
	}

	public function addCentury(int $interval = 1)
	{
		return $this->add($interval * 100, 'year');
	}

	public function addCenturies(int $interval)
	{
		return $this->add($interval * 100, 'year');
	}

	public function addMillenium(int $interval = 1)
	{
		return $this->add($interval * 1000, 'year');
	}

	public function addMillenia(int $interval)
	{
		return $this->add($interval * 1000, 'year');
	}

	/*--------------------------*
	 *	sub~ shorthands
	 *--------------------------*/

	public function subSecond(int $interval = 1)
	{
		return $this->addSecond(-$interval);
	}

	public function subSeconds(int $interval)
	{
		return $this->addSeconds(-$interval);
	}

	public function subMinute(int $interval = 1)
	{
		return $this->addMinute(-$interval);
	}

	public function subMinutes(int $interval)
	{
		return $this->addMinutes(-$interval);
	}

	public function subHour(int $interval = 1)
	{
		return $this->addHour(-$interval);
	}

	public function subHours(int $interval)
	{
		return $this->addHours(-$interval);
	}

	public function subDay(int $interval = 1)
	{
		return $this->addDay(-$interval);
	}

	public function subDays(int $interval)
	{
		return $this->addDays(-$interval);
	}

	public function subMonth(int $interval = 1)
	{
		return $this->addMonth(-$interval);
	}

	public function subMonths(int $interval)
	{
		return $this->addMonths(-$interval);
	}

	public function subQuarter(int $interval = 1)
	{
		return $this->addQuarter(-$interval);
	}

	public function subQuarters(int $interval)
	{
		return $this->addQuarters(-$interval);
	}

	public function subYears(int $interval)
	{
		return $this->addYears(-$interval);
	}

	public function subDecade(int $interval = 1)
	{
		return $this->addDecade(-$interval);
	}

	public function subDecades(int $interval)
	{
		return $this->addDecades(-$interval);
	}

	public function subCentury(int $interval = 1)
	{
		return $this->addCentury(-$interval);
	}

	public function subCenturies(int $interval)
	{
		return $this->addCenturies(-$interval);
	}

	public function subMillenium(int $interval = 1)
	{
		return $this->addMillenium(-$interval);
	}

	public function subMillenia(int $interval)
	{
		return $this->addMillenia(-$interval);
	}

}

