<?php
namespace Dately\Traits;

use Dately\Ground\Dately;
use DateTime;
use DateInterval;


trait Compares
{

	public function equals($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) == 0;
	}

	public function eq($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) == 0;
	}

	public function greaterThan($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) > 0;
	}

	public function gt($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) > 0;
	}

	public function greaterOrEqual($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) >= 0;
	}

	public function gteq($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) >= 0;
	}

	public function lessThan($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) < 0;
	}

	public function lt($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) < 0;
	}

	public function lessOrEqual($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) <= 0;
	}

	public function lteq($another, bool $ignoreTime = true)
	{
		return static::getIntervalSeconds($another, $this, $ignoreTime) <= 0;
	}

	public function between($before, $after, bool $ignoreTime = true)
	{
		return $this->betweenIncluded($before, $after, $ignoreTime);
	}

	public function betweenIncluded($before, $after, bool $ignoreTime = true)
	{
		return $this->greaterOrEqual($before, $ignoreTime) && $this->lessOrEqual($after, $ignoreTime);
	}

	public function betweenExcluded($before, $after, bool $ignoreTime = true)
	{
		return $this->greaterThan($before, $ignoreTime) && $this->lessThan($after, $ignoreTime);
	}

	public function inWhichGap(int $gap, ...$dates)
	{
		return $this->greaterThan($before) && $this->lessThan($after);
	}

}

