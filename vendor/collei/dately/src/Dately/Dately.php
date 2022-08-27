<?php 
namespace Dately;

use Collei\Basement;
use Dately\Ground\Dately as InnerDately;

class Dately extends InnerDately
{


	public static function init($date = null)
	{
		static::$time = $date ?? time();
	}
}
