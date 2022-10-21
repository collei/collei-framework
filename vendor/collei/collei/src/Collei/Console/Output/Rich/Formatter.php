<?php
namespace Collei\Console\Output\Rich;

use Collei\Console\Console;
use Collei\Console\Co;
use Collei\Support\Geometry\Rect;
use Collei\Support\Geometry\Point;

/**
 *	Encapsulates rich text formatting
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-xx
 */
class Formatter
{
	/**
	 *	@var string $text
	 */
	private $text = '';

	/**
	 *	@var array $execute
	 */
	private $execute = [];

	/**
	 *	Set current console color
	 *
	 *	@param	string	$color	
	 *	@param	bool	$background
	 *	@return	void
	 */
	private function fetchColorAndSet(string $color, bool $background = false)
	{
		if ($background)
		{
			Co::setBackgroundColor($color);
		}
		else
		{
			Co::setColor($color);
		}
	}

	/**
	 *	Parse text for console formatter tags and generates console formatting array
	 *
	 *	@param	string	$text
	 *	@return	void
	 */
	private function processTags(string $text)
	{
		$writer = [];
		$commands = $this->consume($text);
		$patterns = [
			'start' => '/^<(\s*fg\s*=\s*([^;>]+))?\s*;?(\s*bg\s*=\s*([^;>]+))?;?(\s*options\s*=\s*(\w+(\s*,\s*\w+)*)\s*)?\s*>$/',
			'auto' => '/^<\s*(\w+)\s*>$/',
			'end' => '/^<\s*\/\s*>$/',
		];

		foreach ($commands as $command)
		{
			$matches = [];

			if (preg_match($patterns['start'], $command, $matches))
			{
				$colors = [
					'fg' => strtolower(trim($matches[2] ?? 'default')),
					'bg' => strtolower(trim($matches[4] ?? 'default')),
				];
				$options = strtolower($matches[6] ?? '');

				if (!empty($options))
				{
					$options = explode(',', $options);

					foreach ($options as $option)
					{
						$writer[] = [
							'verb' => trim($option),
							'argument' => null
						];
					}
				}

				foreach ($colors as $type => $color)
				{
					$writer[] = [
						'verb' => $type,
						'argument' => $color
					];
				}
			}
			elseif (preg_match($patterns['auto'], $command, $matches))
			{
				$writer[] = [
					'verb' => trim($matches[1]),
					'argument' => null,
				];
			}
			elseif (preg_match($patterns['end'], $command, $matches))
			{
				$writer[] = [
					'verb' => 'reset',
					'argument' => null,
				];
			}
			else
			{
				$writer[] = [
					'verb' => 'echo',
					'argument' => $command,
				];
			}
		}

		$this->execute = $writer;
	}

	/**
	 *	Split text among tags and content text
	 *
	 *	@param	string	$text
	 *	@return	array
	 */
	private function consume(string $text)
	{
		$chars = str_split($text);
		$command = [];
		$current = '';

		foreach ($chars as $ch)
		{
			if ($ch == '<')
			{
				$command[] = ($current);
				$current = '';
			}

			$current .= $ch;

			if ($ch == '>')
			{
				$command[] = ($current);
				$current = '';
			}
		}

		$command[] = $current;

		return $command;
	}

	/**
	 *	Execute the console formatting array, printing text and so on
	 *
	 *	@return	void
	 */
	private function execute()
	{
		$effects = [];
		$pures = [
			'br' => "\r\n",
			'cr' => "\r",
			'lf' => "\n",
			'tab' => "\x09",
			'bell' => "\x07",
			'bs' => "\x08",
			'del' => "\x7F",
		];

		foreach ($this->execute as $command)
		{
			$verb = strtolower($command['verb']);
			$argument = $command['argument'] ?? '';

			if ($verb == 'reset')
			{
				Co::resetLast();
				$lastEffect = array_pop($effects);
				//
				if ($lastEffect === 1) // color
				{
					Co::resetColors();
				}
				elseif ($lastEffect === 2) // decoration
				{
					Co::resetColors();
				}
			}
			elseif ($verb == 'b' || $verb == 'bold')
			{
				Co::setBold();
				$effects[] = 2;
			}
			elseif ($verb == 'u' || $verb == 'underscore' || $verb == 'underline')
			{
				Co::setUnderline();
				$effects[] = 2;
			}
			elseif ($verb == 'r' || $verb == 'reverse' || $verb == 'reversed')
			{
				Co::setReversed();
				$effects[] = 2;
			}
			elseif ($verb == 'fg')
			{
				$this->fetchColorAndSet($argument, false);
				$effects[] = 1;
			}
			elseif ($verb == 'bg')
			{
				$this->fetchColorAndSet($argument, true);
				$effects[] = 1;
			}
			elseif (array_key_exists($verb, $pures))
			{
				echo $pures[$verb];
			}
			elseif ($verb == 'echo')
			{
				echo $argument;
			}
			else
			{
				Co::setColor($verb);
				$effects[] = 1;
			}
		}
	}

	/**
	 *	Execute the console formatting array, printing text and so on
	 *	inside a virtual rect
	 *
	 *	@param	\Collei\Geometry\Rect	$rect
	 *	@return	void
	 */
	private function executeInto(Rect $rect)
	{
		$limits = $rect;
		$dot = $rect->origin;
		$overflow = false;
		$effects = [];
		$pures = [
			'br' => "\r\n",
			'cr' => "\r",
			'lf' => "\n",
			'tab' => "\x09",
			'bell' => "\x07",
			'bs' => "\x08",
			'del' => "\x7F",
		];
		//
		Co::moveTo($dot->x, $dot->y);
		//
		foreach ($this->execute as $command)
		{
			if ($overflow) {
				break;
			}
			//
			$verb = strtolower($command['verb']);
			$argument = $command['argument'] ?? '';
			//
			if ($verb == 'reset')
			{
				Co::resetLast();
				$lastEffect = array_pop($effects);
				//
				if ($lastEffect === 1) // color
				{
					Co::resetColors();
				}
				elseif ($lastEffect === 2) // decoration
				{
					Co::resetColors();
				}
			}
			elseif ($verb == 'b' || $verb == 'bold')
			{
				Co::setBold();
				$effects[] = 2;
			}
			elseif ($verb == 'u' || $verb == 'underscore' || $verb == 'underline')
			{
				Co::setUnderline();
				$effects[] = 2;
			}
			elseif ($verb == 'r' || $verb == 'reverse' || $verb == 'reversed')
			{
				Co::setReversed();
				$effects[] = 2;
			}
			elseif ($verb == 'fg')
			{
				$this->fetchColorAndSet($argument, false);
				$effects[] = 1;
			}
			elseif ($verb == 'bg')
			{
				$this->fetchColorAndSet($argument, true);
				$effects[] = 1;
			}
			elseif (array_key_exists($verb, $pures))
			{
				//echo $pures[$verb];
			}
			elseif ($verb == 'echo')
			{
				while (!empty($argument)) {
					$slen = \strlen($argument);
					$avail = $limits->right - $dot->x;
					//
					if ($slen >= $avail) {
						echo \substr($argument, 0, $avail);
						$argument = \substr($argument, $avail);
						$dot = Point::new($limits->left, $dot->y + 1);
						Co::moveTo($dot->x, $dot->y);
					} else {
						echo $argument;
						$argument = '';
						$dot = Point::right($dot, $slen);
					}
					//
					if (!$limits->contains($dot)) {
						$argument = '';
						$overflow = true;
						Co::resetLast();
					}
				}
			}
			else
			{
				Co::setColor($verb);
				$effects[] = 1;
			}
		}
		//
		Co::moveTo(0, $limits->bottom + 2);
	}

	/**
	 *	Instantiate the class
	 *
	 */
	public function __construct()
	{
	}

	/**
	 *	Define the text to be processed and outputted
	 *
	 *	@param	string	$text
	 *	@return	\Collei\Console\Output\Rich\Formatter
	 */
	public function set(string $text)
	{
		$this->text = $text;

		return $this;
	}

	/**
	 *	Process tags
	 *
	 *	@return	\Collei\Console\Output\Rich\Formatter
	 */
	public function process()
	{
		$this->processTags($this->text);

		return $this;
	}

	/**
	 *	Outputs the formatted text
	 *
	 *	@return	void
	 */
	public function output()
	{
		$this->execute();
	}

	/**
	 *	Outputs the formatted text from the $x column and $y row
	 *
	 *	@param	int		$x
	 *	@param	int		$y
	 *	@return	void
	 */
	public function outputTo(int $x, int $y)
	{
		Co::moveTo($x, $y);
		$this->execute();
	}

	/**
	 *	Outputs the formatted text from the $x column and $y row
	 *
	 *	@param	\Collei\Geometry\Rect	$rect
	 *	@return	void
	 */
	public function outputToRect(Rect $rect)
	{
		$this->executeInto($rect);
	}

	/**
	 *	Generate a new Formatter instance with the text set
	 *
	 *	@param	string	$text
	 *	@return	\Collei\Console\Output\Rich\Formatter
	 */
	public static function make(string $text)
	{
		return (new static())->set($text)->process();
	}

}


