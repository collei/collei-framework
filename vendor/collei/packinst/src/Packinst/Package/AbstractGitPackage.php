<?php

namespace Packinst\Package;

use Packinst\Package\Downloader;

/**
 *	@author	alarido.su@gmail.com
 *	@since	2022-06-19
 *
 *	Just basic capabilities of a Git package
 *
 */
abstract class AbstractGitPackage
{
	/**
	 *	@const string META_URI_API
	 */
	public const META_URI_API_INFO = 'https://api.github.com/repos/:group/:project';

	/**
	 *	@const string META_URI_API
	 */
	public const META_URI_API_DOWNLOAD = 'https://api.github.com/repos/:group/:project/zipball/:branch';

	/**
	 *	@const string META_URI_BROWSER
	 */
	public const META_URI_BROWSER = 'http://github.com/:group/:project/archive/:branch.zip';

	/**
	 *	@const string UA
	 */
	protected const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0';

	/**
	 *	@var string $group
	 */
	protected $group;

	/**
	 *	@var string $project
	 */
	protected $project;

	/**
	 *	@var string $repoInfo
	 */
	protected $repositoryInfo = null;

	/**
	 *	Initializes a new package info
	 *
	 *	@param	string	$group
	 *	@param	string	$project
	 *	@return	self
	 */
	public function __construct(string $group, string $project)
	{
		$this->group = $group;
		$this->project = $project;
	}

	/**
	 *	Retrieves meta-info on the repository from Github 
	 *
	 *	@return	self
	 */
	public function __get(string $name)
	{
		if (empty($this->repositoryInfo))
		{
			return;
		}
		//
		if ($name == 'repositoryInfo')
		{
			return $this->repositoryInfo;
		}
		//
		return $this->repositoryInfo->$name ?? null;
	}

	/**
	 *	Retrieves meta-info on the repository from Github 
	 *
	 *	@return	self
	 */
	public function fetchRepositoryInfo()
	{
		$uriInfo = $this->getApiInfoUri();
		//
		$options = [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_BINARYTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_USERAGENT => self::UA,
			CURLOPT_URL => $uriInfo,
		];
		//
		$curlHandle = curl_init();
		curl_setopt_array($curlHandle, $options);
		$result = curl_exec($curlHandle);
		$errstr = curl_error($curlHandle);
		curl_close($curlHandle);
		//
		if ($result && empty($errstr))
		{
			$jsonStr = json_decode($result);
			//
			if (json_last_error() == JSON_ERROR_NONE)
			{
				$this->repositoryInfo = $jsonStr;
			}
		}
	}

	/**
	 *	Returns an API URI for the package
	 *
	 *	@param	string	$branch = null
	 *	@return	string
	 */
	abstract public function getApiUri(string $branch = null);

	/**
	 *	Returns an API URI for the package
	 *
	 *	@param	string	$branch = null
	 *	@return	string
	 */
	abstract public function getApiInfoUri(string $branch = null);

	/**
	 *	Returns an user-browseable URI for the package
	 *
	 *	@param	string	$branch = null
	 *	@return	string
	 */
	abstract public function getBrowserUri(string $branch = null);

}


