<?php
namespace Collei\Console\Output\Grids;

use Collei\Geometry\Rect;
use Collei\Geometry\Point;
use Collei\Console\Output\Grids\TableCell;
use Collei\Console\Output\OutputFormat;
use Collei\Console\Output\OutputFormatInterface;
use Collei\Console\Console;
use Collei\Console\Co;
use Collei\Utils\Str;
use InvalidArgumentException;

/**
 *	Encapsulates a Table cell
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-08-01
 */
class Table
{
	public const PROPERTIES = ['width','format','origin'];
	public const MAX_COLUMNS = 34;

	/**
	 *	@var \Collei\Geometry\Point $origin
	 */
	private $origin;

	/**
	 *	@var int $width
	 */
	private $width;

	/**
	 *	@var int $headerHeight
	 */
	private $headerHeight;

	/**
	 *	@var array $rows
	 */
	private $columnNames = [];

	/**
	 *	@var array $header
	 */
	private $header = [];

	/**
	 *	@var array $rows
	 */
	private $rows = [];

	/**
	 *	@var \Collei\Console\Output\OutputFormatInterface $format
	 */
	private $format;

	/**
	 *	@var \Collei\Console\Output\OutputFormatInterface $headerFormat
	 */
	private $headerFormat;

	/**
	 *	@var \Collei\Console\Output\OutputFormatInterface $borderFormat
	 */
	private $borderFormat;

	/**
	 *	@var \Collei\Console\Output\OutputFormatInterface $gridFormat
	 */
	private $gridFormat;

	/**
	 *	@var bool $hasRowDivisors
	 */
	private $hasRowDivisors = false;

	/**
	 *	Generates a brand new row for adding to the table
	 *
	 *	@param	int	$count
	 *	@return	array
	 *	@throws \InvalidArgumentException
	 */
	private function createRow(int $count)
	{
		if ($count < 1) {
			throw new InvalidArgumentException(
				'Column count must be at least 1.'
			);
		}
		if ($count > self::MAX_COLUMNS) {
			throw new InvalidArgumentException(
				'Column count cannot be greater than ' . self::MAX_COLUMNS . '.'
			);
		}
		//
		$widths = $this->width / $count;
		//
		if ($widths < 1) {
			throw new InvalidArgumentException(
				'Too many columns for such table width.'
			);
		}
		//
		$row = [];
		//
		for ($i = 0; $i < $count; ++$i) {
			$row[] = (new TableCell())
				->setRect(Rect::new(0, 0, $widths, $this->headerHeight))
				->setFormat($this->format);
		}
		//
		return $row;
	}

	/**
	 *	Generates a table header row from column count
	 *
	 *	@param	int	$count
	 *	@return	void
	 */
	protected function generateHeader(int $count)
	{
		$row = $this->createRow($count);
		$this->columnNames = [];
		//
		foreach ($row as $i => $cell) {
			$name = 'C' . ($i + 1);
			$cell->setText($name);
			$this->columnNames[] = $name;
		}
		//
		$this->header = $row;
	}

	/**
	 *	Generates a table header row from an array of column titles
	 *
	 *	@param	array	$names
	 *	@return	void
	 */
	protected function generateHeaderFromColumnNames(array $names)
	{
		if (empty($names)) {
			throw new InvalidArgumentException(
				'Cannot create a table with zero columns.'
			);
		}
		//
		$count = count($names);
		$row = $this->createRow($count);
		$this->columnNames = [];
		//
		foreach ($row as $i => $cell) {
			$name = $names[$i] ?? '';
			//
			if (!is_string($name)) {
				throw new InvalidArgumentException(
					'First argument must be an array of string.'
				);
			}
			//
			$cell->setText($name);
			$this->columnNames[] = $name;
		}
		//
		$this->header = $row;
	}

	/**
	 *	Restore default format configuration
	 *
	 *	@return	self
	 */
	public function setDefaultFormats()
	{
		$this->format = OutputFormat::fromTag('default', '');
		$this->headerFormat = OutputFormat::fromTag('default', '<fg=#ffffff>');
		$this->borderFormat = OutputFormat::fromTag('default', '<fg=#ffffff>');
		$this->gridFormat = OutputFormat::fromTag('default', '<fg=#ffffff>');
		//
		return $this;
	}

	/**
	 *	Sets whether row divisors must be rendered or not
	 *
	 *	@return	self
	 */
	public function setRowDivisors(bool $hasDivisors)
	{
		$this->hasRowDivisors = $hasDivisors;
		return $this;
	}

	/**
	 *	Creates a new TableCell instance
	 *
	 *	@param	int|array	$columns
	 *	@param	\Collei\Geometry\Point	$from
	 *	@param	int	$width
	 */
	public function __construct(
		$columns,
		Point $from,
		int $width,
		int $headerHeight = 1,
		OutputFormatInterface $format = null
	) {
		$this->origin = $from;
		$this->width = $width;
		$this->headerHeight = (($headerHeight < 1) ? 1 : $headerHeight);
		$this->setDefaultFormats();
		//
		if (!empty($format)) {
			$this->format = $format;
		}
		//
		if (is_int($columns)) {
			$this->generateHeader($columns);
		} elseif (is_array($columns)) {
			$this->generateHeaderFromColumnNames($columns);
		} else {
			throw new InvalidArgumentException(
				'First argument must be an integer or an array of string.'
			);
		}
	}

	/**
	 *	@property int $left
	 *	@property int $top
	 *	@property int $right
	 *	@property int $bottom
	 *	@property int $columnCount
	 *	@property int $rowCount
	 *	@property int $width
	 *	@property \Collei\Console\Output\OutputFormatInterface $format
	 *	@property \Collei\Geometry\Point $origin
	 */
	public function __get(string $name)
	{
		if (in_array($name, self::PROPERTIES, TRUE)) {
			return $this->$name;
		}
		//
		switch ($name) {
			case 'left':
				return $this->origin->x;
			case 'top':
				return $this->origin->y;
			case 'right':
				return $this->origin->x + $this->width;
			case 'bottom':
				$bottom = $this->origin->y + $this->header[0]->height + 2;
				foreach ($this->rows as $row) {
					if (isset($row[0]) && ($row[0] instanceof TableCell)) {
						$bottom += $row[0]->height + 1;
					}
				}
				return $bottom;
			case 'columnCount':
				return \count($this->header);
			case 'rowCount':
				return 1 + \count($this->rows);
		}
	}

	/**
	 *	Defines a default format to the table data content
	 *
	 *	@param	Collei\Console\Output\OutputFormatInterface	$format
	 *	@return	self
	 */
	public function setFormat(OutputFormatInterface $format)
	{
		$this->format = $format;
		return $this;
	}

	/**
	 *	Defines a default format to the table header content
	 *
	 *	@param	Collei\Console\Output\OutputFormatInterface	$format
	 *	@return	self
	 */
	public function setHeaderFormat(OutputFormatInterface $format)
	{
		$this->headerFormat = $format;
		//
		foreach ($this->header as $cell) {
			$cell->setFormat($format);
		}
		//
		return $this;
	}

	/**
	 *	Defines a default format to the table outer border and header divisor
	 *
	 *	@param	Collei\Console\Output\OutputFormatInterface	$format
	 *	@return	self
	 */
	public function setBorderFormat(OutputFormatInterface $format)
	{
		$this->borderFormat = $format;
		return $this;
	}

	/**
	 *	Defines a default format to the table inner borders (grid)
	 *
	 *	@param	Collei\Console\Output\OutputFormatInterface	$format
	 *	@return	self
	 */
	public function setGridFormat(OutputFormatInterface $format)
	{
		$this->gridFormat = $format;
		return $this;
	}

	/**
	 *	Adds a row
	 *
	 *	@return	self
	 */
	public function addRow()
	{
		$this->rows[] = $this->createRow(count($this->columnNames));
		return $this;
	}

	/**
	 *	Inserts a row at the given $position
	 *
	 *	@param	int	$position
	 *	@return	self
	 */
	public function insertRow(int $position)
	{
		$rowCount = count($this->rows);
		//
		if ($position >= $rowCount) {
			return $this->addRow();
		}
		//
		$columnCount = count($this->columnNames);
		$row = $this->createRow($columnCount);
		//
		if ($position < 1) {
			\array_unshift($this->rows, $row);
		} else {
			\array_splice($this->rows, $position, 0, [$row]);
		}
		//
		return $this;
	}

	/**
	 *	Removes the row from the given $position
	 *
	 *	@param	int	$position
	 *	@return	self
	 */
	public function removeRow(int $position)
	{
		$rowCount = count($this->rows);
		//
		if ($position >= $rowCount) {
			\array_pop($this->rows);
			return $this->addRow();
		}
		//
		$columnCount = count($this->columnNames);
		$row = $this->createRow($columnCount);
		//
		if ($position < 1) {
			\array_shift($this->rows);
		} else {
			\array_splice($this->rows, $position, 1);
		}
		//
		return $this;
	}

	/**
	 *	Sets the content of the given cell at $row and $column
	 *
	 *	@param	int	$row
	 *	@param	int	$column
	 *	@param	string	$text
	 *	@return	self
	 */
	public function setCell(int $row, int $column, string $text)
	{
		$rowCount = count($this->rows);
		$columnCount = count($this->columnNames);
		//
		if (($row < 1) || ($row > $rowCount)) {
			throw new InvalidArgumentException(
				"Row # must be between 1 and {$rowCount}, inclusive."
			);
		}
		if (($column < 1) || ($column > $columnCount)) {
			throw new InvalidArgumentException(
				"Column # must be between 1 and {$columnCount}, inclusive."
			);
		}
		//
		$this->rows[--$row][--$column]->setText($text);
		//
		return $this;
	}

	/**
	 *	Returns the content of the given cell at $row and $column
	 *
	 *	@param	int	$row
	 *	@param	int	$column
	 *	@return	string
	 */
	public function getCell(int $row, int $column)
	{
		$rowCount = count($this->rows);
		$columnCount = count($this->columnNames);
		//
		if (($row < 1) || ($row > $rowCount)) {
			throw new InvalidArgumentException(
				"Row # must be between 1 and {$rowCount}, inclusive."
			);
		}
		if (($column < 1) || ($column > $columnCount)) {
			throw new InvalidArgumentException(
				"Column # must be between 1 and {$columnCount}, inclusive."
			);
		}
		//
		return $this->rows[--$row][--$column]->getText() ?? '';
	}

	/**
	 *	Renders the table
	 *
	 *	@return	self
	 */
	public function render()
	{
		$base = Point::right($this->origin, 1);
		$inter = 1;
		$current = $base->copy();
		//
		$this->borderFormat->writeTo(
			Str::repeat('-', $this->width + count($this->header) * ($inter + 1) + 1),
			$current->x - 1,
			$current->y
		);
		//
		$current = Point::up($current, 1);
		//
		foreach ($this->header as $headerCell) {
			$headerCell->render($current);
			$current = Point::right($current, $headerCell->width + $inter + 1);
		}
		//
		$current = Point::new($base->x, $current->y + 1);
		//
		$this->borderFormat->writeTo(
			Str::repeat('-', $this->width + count($this->header) * ($inter + 1) + 1),
			$current->x - 1,
			$current->y
		);
		//
		$current = Point::up($current, 1);
		//
		foreach ($this->rows as $row) {
			foreach ($row as $cell) {
				$cell->render($current);
				$current = Point::right($current, $cell->width + $inter + 1);
			}
			//
			echo "\0337\033[1B\r\n\0338";
			//
			$current = Point::new(
				$base->x,
				$current->y + $row[0]->height + ($this->hasRowDivisors ? 1 : 0)
			);
		}
		//
		$this->borderFormat->writeTo(
			Str::repeat('-', $this->width + count($this->header) * ($inter + 1) + 1),
			$current->x - 1,
			$current->y - ($this->hasRowDivisors ? 0 : 0)
		);
		//
		return $this;
	}

}

