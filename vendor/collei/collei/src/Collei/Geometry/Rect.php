<?php
namespace Collei\Geometry;

use Collei\Geometry\Point;

/**
 *	This represents a Rectangle
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2022-08-03
 */
class Rect
{
	private const CONCRETE = ['left','top','width','height'];

	/**
	 *	@var int $left
	 */
	private $left;

	/**
	 *	@var int $top
	 */
	private $top;

	/**
	 *	@var int $width
	 */
	private $width;

	/**
	 *	@var int $height
	 */
	private $height;

	/**
	 *	@property int $left
	 *	@property int $top
	 *	@property int $width
	 *	@property int $height
	 *	@property int $right
	 *	@property int $bottom
	 */
	public function __get(string $name)
	{
		if (in_array($name, self::CONCRETE, TRUE)) {
			return $this->$name;
		}
		if ($name == 'origin') {
			return Point::new($this->left, $this->top);
		}
		if ($name == 'right') {
			return $this->left + $this->width - 1;
		}
		if ($name == 'bottom') {
			return $this->top + $this->height - 1;
		}
	}

	/**
	 *	Creates a new Rect
	 *
	 *	@param	int	$left
	 *	@param	int	$top
	 *	@param	int	$width
	 *	@param	int	$height
	 */
	public function __construct(int $left, int $top, int $width, int $height)
	{
		$this->left = $left;
		$this->top = $top;
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 *	For PHP internal use
	 *
	 *	@return	array
	 */
	public function __debugInfo()
	{
		return [
			'left' => $this->left,
			'top' => $this->top,
			'right' => $this->left + $this->width,
			'bottom' => $this->top + $this->height,
			'width' => $this->width,
			'height' => $this->height
		];
	}

	/**
	 *	Tells if Point is inside Rect or not
	 *
	 *	@param	\Collei\Geometry\Point	$point
	 *	@return	bool
	 */
	public function contains(Point $point)
	{
		return ($point->x >= $this->left)
			&& ($point->x <= ($this->left + $this->width))
			&& ($point->y >= $this->top)
			&& ($point->y < ($this->top + $this->height));
	}

	/**
	 *	Creates a new Rect on the fly
	 *
	 *	@param	int	$left
	 *	@param	int	$top
	 *	@param	int	$width
	 *	@param	int	$height
	 */
	public static function new(int $left, int $top, int $width, int $height)
	{
		return new self($left, $top, $width, $height);
	}

	/**
	 *	Creates a new Rect from a couple Point instances
	 *
	 *	@param	int	$x0
	 *	@param	int	$y0
	 *	@param	int	$x1
	 *	@param	int	$y1
	 */
	public static function fromPoints(Point $topLeft, Point $bottomRight)
	{
		return new self(
			$topLeft->x,
			$topLeft->y,
			$bottomRight->x - $topLeft->x,
			$bottomRight->y - $topLeft->y
		);
	}

	/**
	 *	Creates a new Rect from coordinates
	 *
	 *	@param	int	$x0
	 *	@param	int	$y0
	 *	@param	int	$x1
	 *	@param	int	$y1
	 */
	public static function fromCoordinates(int $x0, int $y0, int $x1, int $y1)
	{
		return new self($x0, $y0, $x1 - $x0, $y1 - $y0);
	}

}

