<?php
namespace Collei\Support\Parsers;

use Collei\Support\Values\Capsule;

/**
 *	Handle raw request input from php://input (e.g., PUT, PATCH requests)
 *
 *	Adapted from jas-/clasxs.stream.php work
 *		@link https://gist.github.com/jas-/5c3fdc26fedd11cb9fb5
 *
 *	LICENSE: This source file is subject to version 3.01 of the GPL license
 *	that is available through the world-wide-web at the following URI:
 *	http://www.gnu.org/licenses/gpl.html. If you did not receive a copy of
 *	the GPL License and are unable to obtain it through the web, please
 *
 *	@author jason.gerfen@gmail.com @since 2014-07-30
 *	@license http://www.gnu.org/licenses/gpl.html GPL License 3
 *	@derivedworkby alarido.su@gmail.com @since 2022-06-02
 *
 */
class RawRequestBodyParser
{
	/**
	 *	@var string $input
	 */
	protected $input;

	/**
	 *	@var array $files
	 */
	protected $files = [];

	/**
	 *	@var array $fields
	 */
	protected $fields = [];

	/**
	 *	Organizes data
	 *
	 *	@method	fetchData
	 *	@param	array	$data
	 *	@return	void
	 */
	private function fetchData(array $data)
	{
		$this->fields = $data['post'];
		$this->files = $data['file'];
	}

	/**
	 *	Performs data capture operation
	 *
	 *	@method captureData
	 *	@param	array	&$data
	 *	@return	void
	 */
	private function captureData(array &$data, string $bytes)
	{
		$this->input = $bytes;
		//
		$boundary = $this->boundary();
		//
		if (!strlen($boundary))
		{
			$data = [
				'post' => $this->parse($this->input),
				'file' => []
			];
		}
		else
		{
			$data = $this->blocks($this->split($boundary));
		}
	}

	/**
	 *	Detects and returns the boundary instances
	 *
	 *	@method array boundary
	 *	@return Array
	 */
	private function boundary()
	{
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'] ?? '', $matches);
		//
		return $matches[1] ?? '';
	}

	/**
	 *	Parses the basic query string formatted data
	 *
	 *	@method parse
	 *	@return	Array
	 */
	private function parse()
	{
		parse_str(urldecode($this->input), $result);
		//
		return $result;
	}

	/**
	 *	Split the input in chunks using the detected $boundary
	 *
	 *	@method	split
	 *	@param	string	$boundary
	 *	@return	Array
	 */
	private function split(string $boundary)
	{
		$result = preg_split("/-+$boundary/", $this->input);
		//
		// get hid from the last
		array_pop($result);
		//
		return $result;
	}

	/**
	 *	Deals with the parsed data blocks from the raw request
	 *
	 *	@method	blocks
	 *	@param	array	$array
	 *	@return	Array
	 */
	private function blocks(array $array)
	{
		$results = [
			'post' => [],
			'file' => []
		];
		//
		foreach($array as $key => $value)
		{
			if (empty($value)) continue;
			//
			$block = $this->decide($value);
			//
			if (count($block['post']) > 0)
			{
				array_push($results['post'], $block['post']);
			}
			//
			if (count($block['file']) > 0)
			{
				array_push($results['file'], $block['file']);
			}
		}
		//
		return $this->merge($results);
	}

	/**
	 *	A suitable description is not available yet.
	 *
	 *	@method decide
	 *	@param	string	$string
	 *	@return	Array
	 */
	private function decide(string $string)
	{
		if (strpos($string, 'application/octet-stream') !== FALSE)
		{
			return [
				'post' => $this->file($string),
				'file' => []
			];
		}
		//
		if (strpos($string, 'filename') !== FALSE)
		{
			return [
				'post' => [],
				'file' => $this->file_stream($string)
			];
		}
		//
		return [
			'post' => $this->post($string),
			'file' => []
		];
	}

	/**
	 *	Parses the string into an uploaded file data descriptor 
	 *
	 *	@method	file
	 *	@param	string	$string
	 *	@return	Array
	 */
	private function file(string $string)
	{
		preg_match(
			'/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s',
			$string, $match
		);
		//
		return array(
			$match[1] => $match[2] ?? ''
		);
	}

	/**
	 *	Handles file data, emulating $_FILES superglobal behavior
	 *
	 *	@method file_stream
	 *	@param	string	$string
	 *	@return	Array
	 */
	private function file_stream(string $string)
	{
		$data = array();
		//
		preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $string, $match);
		preg_match('/Content-Type: (.*)?/', $match[3], $mime);
		//
		// Prepares file data
		$image = preg_replace('/Content-Type: (.*)[^\n\r]/', '', $match[3]);
		//
		// generates temporary file name
		$path = @tempnam(sys_get_temp_dir(), 'collei');
		//
		// saves the file to a temporary location
		$err = file_put_contents($path, ltrim($image));
		//
		if (preg_match('/^(.*)\[\]$/i', $match[1], $tmp))
		{
			$index = $tmp[1];
		}
		else
		{
			$index = $match[1];
		}
		//
		$data[$index]['name'][] = $match[2];
		$data[$index]['type'][] = $mime[1];
		$data[$index]['tmp_name'][] = $path;
		$data[$index]['error'][] = ($err === FALSE) ? $err : 0;
		$data[$index]['size'][] = filesize($path);
		//
		return $data;
	}

	/**
	 *	Parse post fields.
	 *
	 *	@method post
	 *	@param	string	$string
	 *	@return Array
	 */
	private function post(string $string)
	{
		$data = [];
		//
		preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $string, $match);
		//
		if (preg_match('/^(.*)\[\]$/i', $match[1], $tmp))
		{
			$data[$tmp[1]][] = $match[2] ?? '';
		}
		else
		{
			$data[$match[1]] = $match[2] ?? '';
		}
		//
		return $data;
	}

	/**
	 *	Executes complex array merging
	 *
	 *	@method merge
	 *	@param	array	$array
	 *	@return	Array
	 */
	private function merge(array $array)
	{
		$results = [
			'post' => [],
			'file' => []
		];
		//
		if (count($array['post']) > 0)
		{
			foreach($array['post'] as $key => $value)
			{
				foreach($value as $k => $v)
				{
					if (is_array($v))
					{
						foreach($v as $kk => $vv)
						{
							$results['post'][$k][] = $vv;
						}
					}
					else
					{
						$results['post'][$k] = $v;
					}
				}
			}
		}
		//
		if (count($array['file']) > 0)
		{
			foreach($array['file'] as $key => $value)
			{
				foreach($value as $k => $v)
				{
					if (is_array($v))
					{
						foreach($v as $kk => $vv)
						{
							if (is_array($vv) && (count($vv) == 1))
							{
								$results['file'][$k][$kk] = $vv[0];
							}
							else
							{
								$results['file'][$k][$kk][] = $vv[0] ?? $vv;
							}
						}
					}
					else
					{
						$results['file'][$k][$key] = $v;
					}
				}
			}
		}
		//
		return $results;
	}


	/**
	 *	Builds the instance and captures PUT data
	 *
	 *	@method __construct
	 */
	public function __construct(string $bytes = null)
	{
		$data = [];
		//
		if (empty($bytes))
		{
			$bytes = file_get_contents('php://input');
		}
		//
		$this->captureData($data, $bytes);
		$this->fetchData($data);
	}

	/**
	 *	Retrieves fields by their names
	 *
	 *	@method	__get
	 *	@return	mixed
	 */
	public function __get(string $name)
	{
		return $this->fields[$name] ?? null;
	}

	/**
	 *	Retrieves a Capsule with all request fields inside
	 *
	 *	@method	getFields
	 *	@return	mixed
	 */
	public function getFields()
	{
		return Capsule::from($this->fields ?? []);
	}

	/**
	 *	Checks if the given field $name is a file
	 *
	 *	@method	isFile
	 *	@param	string	$name
	 *	@return	mixed
	 */
	public function isFile(string $name)
	{
		return isset($this->files[$name]['tmp_name']);
	}

	/**
	 *	Retrieves info about the $name field file
	 *
	 *	@method	fileInfo
	 *	@param	string	$name
	 *	@return	\Collei\Support\Values\Capsule|false
	 */
	public function fileInfo(string $name)
	{
		if (!$this->isFile($name))
		{
			return false;
		}
		//
		return Capsule::from($this->files[$name]);
	}

	/**
	 *	Copies a file under $name field to the given destination
	 *
	 *	@todo	implement
	 *	@method	saveFileTo
	 *	@param	string	$name
	 *	@param	string	$destination
	 *	@return	mixed
	 */
	public function saveFileTo(string $name, string $destination)
	{
		if (!$this->isFile($name))
		{
			return false;
		}
		//
		if (!$this->isFile($name))
		{
			return false;
		}
	}

}


