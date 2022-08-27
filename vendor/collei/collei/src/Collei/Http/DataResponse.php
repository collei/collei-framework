<?php 
namespace Collei\Http;

use Collei\Http\Response;
use Collei\Http\Traits\MimeTypes;
use Collei\Utils\Collections\Properties;

/**
 *	Encapsulates the servlet response
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-05-xx
 */
class DataResponse extends Response
{
	use MimeTypes;
	
	/**
	 *	@var string $format
	 */
	private $format = 'text/html';

	/**
	 *	@var string $encoding
	 */
	private $encoding = 'utf-8';

	/**
	 *	Create a new instance with specified format
	 *
	 *	@param	string	$format
	 *	@param	string	$encoding
	 *	@return	\Collei\Http\DataResponse
	 */
	public static function make(string $format = 'text/html', string $encoding = 'utf-8')
	{
		$new = new static();

		if (empty($format))
		{
			$format = 'text/html';
		}
		if (empty($encoding))
		{
			$encoding = 'utf-8';
		}

		$new->format = $format;
		$new->encoding = $encoding;

		$new->setHeader('Content-type', "{$format};charset={$encoding}");

		return $new;
	}

	/**
	 *	Set the output for client downloading
	 *
	 *	@param	string	$fileNameDest
	 *	@return	\Collei\Http\DataResponse
	 */
	public function downloadAs(string $fileNameDest = 'file.txt')
	{
		return $this->setHeader('Content-Disposition', "attachment; filename={$fileNameDest}");
	}

	/**
	 *	Set the output for client downloading, forcing it upon several platforms
	 *
	 *	@param	string	$fileNameDest
	 *	@return	\Collei\Http\DataResponse
	 */
	public function forceDownloadAs(string $fileNameDest = 'file.txt')
	{
		return $this->downloadAs($fileNameDest)
				->setHeader('Content-Type', 'application/octet-stream');
	}

	/**
	 *	Defines content of the response body 
	 *
	 *	@param	mixed	$content
	 *	@return	\Collei\Http\DataResponse
	 */
	public function setBody($content)
	{
		return parent::setBody($content);
	}

}