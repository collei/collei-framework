<?php
namespace Collei\Support\Geometry;

/**
 *	Encapsulates a Point
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-08-04
 */
class Point
{
	/**
	 *	@var int $x
	 */
	private $x;

	/**
	 *	@var int $y
	 */
	private $y;

	/**
	 *	Creates a new Point
	 *
	 *	@param	int	$x
	 *	@param	int	$y
	 */
	public function __construct(int $x, int $y)
	{
		$this->x = $x;
		$this->y = $y;
	}

	/**
	 *	@property int $x
	 *	@property int $y
	 */
	public function __get(string $name)
	{
		if ($name == 'x') {
			return $this->x;
		}
		if ($name == 'y') {
			return $this->y;
		}
	}

	/**
	 *	For PHP internal use
	 *
	 *	@return	array
	 */
	public function __debugInfo()
	{
		return [
			'x' => $this->x,
			'y' => $this->y
		];
	}

	/**
	 *	Returns a copy of the Point
	 *
	 *	@return	\Collei\Geometry\Point
	 */
	public function copy()
	{
		return new self($this->x, $this->y);
	}

	/**
	 *	Returns true if both point to same coordinates
	 *
	 *	@return	bool
	 */
	public function equals(Point $anotherPoint)
	{
		return ($this->x === $anotherPoint->x)
			&& ($this->y === $anotherPoint->y);
	}

	/**
	 *	Creates a new Point on the fly
	 *	@static
	 *	@param	int	$x
	 *	@param	int	$y
	 *	@return	\Collei\Geometry\Point
	 */
	public static function new(int $x, int $y)
	{
		return new self($x, $y);
	}

	/**
	 *	Creates a new Point on the fly, vertically shifted by $y
	 *	@static
	 *	@param	\Collei\Geometry\Point	$point
	 *	@param	int	$y
	 *	@return	\Collei\Geometry\Point
	 */
	public static function up(Point $point, int $y)
	{
		return new self($point->x, $point->y + $y);
	}

	/**
	 *	Creates a new Point on the fly, horizontally shifted by $x
	 *	@static
	 *	@param	\Collei\Geometry\Point	$point
	 *	@param	int	$x
	 *	@return	\Collei\Geometry\Point
	 */
	public static function right(Point $point, int $x)
	{
		return new self($point->x + $x, $point->y);
	}

	/**
	 *	Creates a new Point on the fly, shifted at both directions by ($x,$y)
	 *	@static
	 *	@param	\Collei\Geometry\Point	$point
	 *	@param	int	$x
	 *	@param	int	$y
	 *	@return	\Collei\Geometry\Point
	 */
	public static function both(Point $point, int $x, int $y)
	{
		return new self($point->x + $x, $point->y + $y);
	}

}

