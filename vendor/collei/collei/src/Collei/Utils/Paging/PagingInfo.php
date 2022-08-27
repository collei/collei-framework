<?php
namespace Collei\Utils\Paging;

use Collei\Pacts\Jsonable;

/**
 *	Paging info and calculation
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-03-26
 */
class PagingInfo implements Jsonable
{
	/**
	 *	@var int $pageSize - how many lines are in a page
	 */
	private $pageSize;

	/**
	 *	@var int $rowCount - number of lines
	 */
	private $rowCount;

	/**
	 *	@var int $current - current page
	 */
	private $current;

	/**
	 *	@var int $lastPage - last possible page, given line count and page size 
	 */
	private $lastPage;

	/**
	 *	initializes the structure
	 *
	 *	@param	int	$pageSize
	 *	@param	int	$rowCount
	 */
	public function __construct(int $pageSize, int $rowCount = null)
	{
		$rowCount = $rowCount ?? 0;

		if ($pageSize < 1)
		{
			$pageSize = 1;
		}

		if ($rowCount < 0)
		{
			$rowCount = 0;
		}

		$this->pageSize = $pageSize;
		$this->rowCount = $rowCount;
		$this->lastPage = $this->pageFromLine($rowCount);
		$this->current = 1;
	}

	/**
	 *	@property	int	$first
	 *	@property	int	$prior
	 *	@property	int	$next
	 *	@property	int	$last
	 *	@property	int	$pageSize
	 *	@property	int	$rowCount
	 *	@property	int	$current
	 */
	public function __get($name)
	{
		if ($name == 'first')
		{
			return 1;
		}
		if ($name == 'prior')
		{
			return (($this->current > 1) ? ($this->current - 1) : 1);
		}
		if ($name == 'next')
		{
			return (($this->current < $this->lastPage) ? ($this->current + 1) : $this->lastPage);
		}
		if (($name == 'last') || ($name == 'pageCount'))
		{
			return $this->lastPage;
		}
		if (in_array($name, ['pageSize','rowCount','current']))
		{
			return $this->$name;
		}
	}

	/**
	 *	returns the page for the specified global line number
	 *
	 *	@param	int	$line
	 *	@return	int
	 */
	public function pageFromLine(int $line)
	{
		if ($this->rowCount < $this->pageSize)
		{
			return 1;
		}

		if ($line < 1)
		{
			return 1;
		}

		if ($line > $this->rowCount)
		{
			return (empty($this->lastPage) ? 1 : $this->lastPage);
		}

		$page = \intdiv($line, $this->pageSize);

		if (($line % $this->pageSize) > 0)
		{
			++$page;
		}

		return ($this->current = $page);
	}

	/**
	 *	sets the current page
	 *
	 *	@param	int	$page
	 *	@return	void
	 */
	public function setCurrent(int $page)
	{
		if ($page < 1)
		{
			$this->current = 1;
		}
		elseif ($page > $this->lastPage)
		{
			$this->current = $this->lastPage;
		}
		else
		{
			$this->current = $page;
		}
	}

	/**
	 *	converts the object data to Json string
	 *
	 *	@return	string
	 */
	public function toJson()
	{
		return json_encode([
			'first' => $this->first,
			'prior' => $this->prior,
			'next' => $this->next,
			'last' => $this->last,
			'current' => $this->current,
			'pageCount' => $this->pageCount,
			'pageSize' => $this->pageSize,
			'rowCount' => $this->rowCount,
		]);
	}


}

