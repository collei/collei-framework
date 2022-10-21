<?php
namespace Collei\Console\Output;

use Collei\Support\Number;

/**
 *	This class encapsulates a RGB color and some of common conversion routines
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-11-26		
 */
class RGBColor
{
	/**
	 *	@var	int	$red
	 */
	private $red = 0;

	/**
	 *	@var	int	$green
	 */
	private $green = 0;

	/**
	 *	@var	int	$blue
	 */
	private $blue = 0;

	/**
	 *	Buils a RGBColor
	 *
	 *	@param	int	$red	integer red value
	 *	@param	int	$green	integer green value
	 *	@param	int	$blue	integer blue value
	 */
	public function __construct(int $red, int $green, int $blue)
	{
		$this->red = Number::constrict($red, 0, 255);
		$this->green = Number::constrict($green, 0, 255);
		$this->blue = Number::constrict($blue, 0, 255);
	}

	/**
	 *	Converts the RGBColor into 256-int console value
	 *
	 *	@return	int	
	 */
	public function toAnsiConsole256()
	{
		$red = $this->red >> 5;
		$green = $this->green >> 5;
		$blue = $this->blue >> 6;
		//
		return ($red << 5) | ($green << 3) | $blue;
	}

	/**
	 *	Converts the RGBColor into 216-int RGB console value (16~231)
	 *
	 *	@return	int	
	 */
	public function toAnsiConsole216()
	{
		$r_mac = intdiv($this->red, 51);
		$g_mac = intdiv($this->green, 51);
		$b_mac = intdiv($this->blue, 51);
		//
		$color216 = ($r_mac * 36) + ($g_mac * 6) + $b_mac;
		//
		return $color216 + 16;
	}

	/**
	 *	Converts the RGBColor into RGBI array value [r, g, b, i],
	 *	each being either 0 or 1
	 *
	 *	@return	array
	 */
	public function toRGBI()
	{
		return self::rgbi_approx(
			$this->red, $this->green, $this->blue
		);
	}

	/**
	 *	Converts the RGBColor into RGBI integer value, packed as (IRGB)
	 *
	 *	@return	int
	 */
	public function toIntRGBI()
	{
		list($r, $g, $b, $i) = self::rgbi_approx(
			$this->red, $this->green, $this->blue
		);
		//
		return ($i << 3) | ($r << 2) | ($g << 1) | $b;
	}

	////////////////////////
	/// static factories ///
	////////////////////////

	/**
	 *	Buils a RGBColor instance from RGB int values
	 *	@static
	 *	@param	int	$red	integer red value
	 *	@param	int	$green	integer green value
	 *	@param	int	$blue	integer blue value
	 *	@return	\Collei\Console\Output\RGBColor	
	 */
	public static function fromRGB(int $red, int $green, int $blue)
	{
		return new self($red, $green, $blue);
	}

	/**
	 *	Buils a RGBColor instance from hexadecimal value
	 *	@static
	 *	@param	string	$hexval	Hexadecimal RGB value in format #RGB or #RRGGBB
	 *	@return	\Collei\Console\Output\RGBColor	
	 */
	public static function fromHexadecimal(string $hexval)
	{
		list($red, $green, $blue) = self::hexToRGB(trim($hexval));

		return new self($red, $green, $blue);
	}

	//////////////////////
	/// static helpers ///
	//////////////////////

	/**
	 *	Converts from hexadecimal value to 256-int console value
	 *	@static
	 *	@param	string	$hexval	Hexadecimal RGB value in format #RGB or #RRGGBB
	 *	@return	int	
	 */
	public static function hexToAnsiConsole256(string $hexval)
	{
		return self::fromHexadecimal($hexval)->toAnsiConsole256();
	}

	/**
	 *	Converts from hexadecimal value to 216-int RGB console value (16~231)
	 *	@static
	 *	@param	string	$hexval	Hexadecimal RGB value in format #RGB or #RRGGBB
	 *	@return	int	
	 */
	public static function hexToAnsiConsole216(string $hexval)
	{
		return self::fromHexadecimal($hexval)->toAnsiConsole216();
	}

	/**
	 *	Converts the $hexval color into RGBI array value [r, g, b, i], each being either 0 or 1
	 *	@static
	 *	@param	string	$hexval	Hexadecimal RGB value in format #RGB or #RRGGBB
	 *	@return	array	
	 */
	public static function hexToRGBI(string $hexval)
	{
		return self::fromHexadecimal($hexval)->toRGBI();
	}

	/**
	 *	Converts the $hexval color into RGBI integer value, packed as (IRGB)
	 *	@static
	 *	@param	string	$hexval	Hexadecimal RGB value in format #RGB or #RRGGBB
	 *	@return	int	
	 */
	public static function hexToIntRGBI(string $hexval)
	{
		return self::fromHexadecimal($hexval)->toIntRGBI();
	}

	/**
	 *	Converts from hexadecimal value to 256-int console value
	 *	@static
	 *	@param	int	$red	integer red value
	 *	@param	int	$green	integer green value
	 *	@param	int	$blue	integer blue value
	 *	@return	int	
	 */
	public static function rgbToAnsiConsole256(int $red, int $green, int $blue)
	{
		return self::fromRGB($red, $green, $blue)->toAnsiConsole256();
	}

	/**
	 *	Converts from hexadecimal value to 216-int RGB console value (16~231)
	 *	@static
	 *	@param	int	$red	integer red value
	 *	@param	int	$green	integer green value
	 *	@param	int	$blue	integer blue value
	 *	@return	int	
	 */
	public static function rgbToAnsiConsole216(int $red, int $green, int $blue)
	{
		return self::fromRGB($red, $green, $blue)->toAnsiConsole216();
	}

	/**
	 *	Converts the RGB colors into RGBI array value [r, g, b, i],
	 *	each being either 0 or 1
	 *	@static
	 *	@param	int	$red	integer red value
	 *	@param	int	$green	integer green value
	 *	@param	int	$blue	integer blue value
	 *	@return	array	
	 */
	public static function rgbToRGBI(int $red, int $green, int $blue)
	{
		return self::fromRGB($red, $green, $blue)->toRGBI();
	}

	/**
	 *	Converts the RGB colors into RGBI integer value, packed as (IRGB)
	 *	@static
	 *	@param	int	$red	integer red value
	 *	@param	int	$green	integer green value
	 *	@param	int	$blue	integer blue value
	 *	@return	int	
	 */
	public static function rgbToIntRGBI(int $red, int $green, int $blue)
	{
		return self::fromRGB($red, $green, $blue)->toIntRGBI();
	}

	/**
	 *	Converts from hexadecimal value into array of
	 *	RGB integer values (R, G, B)
	 *	@static
	 *	@param	string	$hexval		string hexadecimal, either #RGB or #RRGGBB
	 *	@return	array	
	 */
	public static function hexToRGB(string $hexval)
	{
		$hexval = preg_replace("/[^0-9A-Fa-f]/", '', $hexval);
		$len = strlen($hexval);
		$r = $g = $b = 0;
		//
		if ($len >= 6) {
			$r = hexdec(substr($hexval,0,2)) * 1;
			$g = hexdec(substr($hexval,2,2)) * 1;
			$b = hexdec(substr($hexval,4,2)) * 1;
		} elseif ($len >= 3) {
			$r = hexdec(substr($hexval,0,1)) * 16;
			$g = hexdec(substr($hexval,1,1)) * 16;
			$b = hexdec(substr($hexval,2,1)) * 16;
		} else {
			return false;
		}
		//
		return [$r, $g, $b];
	}

	///////////////////////
	/// private backend ///
	///////////////////////

	/**
	 *	Finds the closest RGBx approximation of a 24-bit RGB color,
	 *	for x = 0 or 1
	 *	@static
	 *	@param	int	$red
	 *	@param	int	$green
	 *	@param	int	$blue
	 *	@param	int	$x
	 *	@return	array
	 */
	private static function rgbx_approx(int $red, int $green, int $blue, int $x)
	{
		$threshold = ($x + 1) * 255 / 3;
		$r = ($red > $threshold ? 1 : 0);
		$g = ($green > $threshold ? 1 : 0);
		$b = ($blue > $threshold ? 1 : 0);
		return [$r, $g, $b];
	}

	/**
	 *	Converts a 4-bit RGBI color back to 24-bit RGB
	 *	@static
	 *	@param	int	$r
	 *	@param	int	$g
	 *	@param	int	$b
	 *	@param	int	$i
	 *	@return	array
	 */
	private static function rgbi_to_rgb24(int $r, int $g, int $b, int $i)
	{
		$red = (2*$r + $i) * 255 / 3;
		$green = (2*$g + $i) * 255 / 3;
		$blue = (2*$b + $i) * 255 / 3;
		//
		return [$red, $green, $blue];
	}

	/**
	 *	Returns the (squared) Euclidean distance between two RGB colors
	 *	@static
	 *	@param	int	$red_a
	 *	@param	int	$green_a
	 *	@param	int	$blue_a
	 *	@param	int	$red_b
	 *	@param	int	$green_b
	 *	@param	int	$blue_b
	 *	@return	array
	 */
	private static function color_distance(
		int $red_a, int $green_a, int $blue_a,
		int $red_b, int $green_b, int $blue_b
	) {
		$d_red = $red_a - $red_b;
		$d_green = $green_a - $green_b;
		$d_blue = $blue_a - $blue_b;
		//
		return ($d_red * $d_red) + ($d_green * $d_green) + ($d_blue * $d_blue);
	}

	/**
	 *	finds the closest 4-bit RGBI approximation (by Euclidean distance)
	 *	to a 24-bit RGB color
	 *	@static
	 *	@param	int	$red
	 *	@param	int	$green
	 *	@param	int	$blue
	 *	@return	array
	 */
	private static function rgbi_approx(int $red, int $green, int $blue)
	{
		// find best RGB0 and RGB1 approximations:
		list($r0, $g0, $b0) = self::rgbx_approx($red, $green, $blue, 0);
		list($r1, $g1, $b1) = self::rgbx_approx($red, $green, $blue, 1);
		// convert them back to 24-bit RGB:
		list($red0, $green0, $blue0) = self::rgbi_to_rgb24($r0, $g0, $b0, 0);
		list($red1, $green1, $blue1) = self::rgbi_to_rgb24($r1, $g1, $b1, 1);
		// return the color closer to the original:
		$d0 = self::color_distance($red, $green, $blue, $red0, $green0, $blue0);
		$d1 = self::color_distance($red, $green, $blue, $red1, $green1, $blue1);
		//
		if ($d0 <= $d1) {
			return [$r0, $g0, $b0, 0];
		} else {
			return [$r1, $g1, $b1, 1];
		}
	}

}
