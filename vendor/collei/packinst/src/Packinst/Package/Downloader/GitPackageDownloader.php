<?php

namespace Packinst\Package\Downloader;

use Packinst\Package\GitPackage;
use Packinst\Package\GithubPackage;

/**
 *	@author	alarido.su@gmail.com
 *	@since	2022-06-18
 *
 *	Class for downloading a GITHUB hosted package
 *
 */
class GitPackageDownloader
{
	/**
	 *	@const string PATTERN
	 */
	private const PATTERN = '/([\w_\-.]+)[\\/\\\\]([\w_\-.]+)/';

	/**
	 *	@const string PATTERN
	 */
	private const TEMP_FETCH = '.tempfetch';

	/**
	 *	@property array $options
	 */
	private $options = [
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_BINARYTRANSFER => 1,
		CURLOPT_FOLLOWLOCATION => 1,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_TIMEOUT =>  28800, // 8 hours
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0',
	];

	/**
	 *	@property \Packinst\Package\GitPackage $package
	 */
	private $package = null;

	/**
	 *	@property string $downloadedLocation
	 */
	private $downloadedLocation = null;

	/**
	 *	@property array $archiveInfo
	 */
	private $archiveInfo = [];

	/**
	 *	@property array $dependencyInfo
	 */
	private $dependencyInfo = [];

	/**
	 *	Performs CURL download operation from $uri and, if successful,
	 *	saves the result to $destination path
	 *
	 *	@param	string	$uri
	 *	@param	string	$destination
	 *	@return	bool
	 */
	private function fetchCurlDownload(string $uri, string $destination)
	{
		if (empty($uri) || empty($destination))
		{
			return false;
		}
		//
		// create file handle for destination
		$to_tried = $destination . self::TEMP_FETCH;
		$fileHandle = fopen($to_tried, 'w');
		//
		// set the needed options
		$options = $this->options;
		$options[CURLOPT_URL] = $uri;
		$options[CURLOPT_FILE] = $fileHandle;
		//
		// performs download operation
		$curlHandle = curl_init();
		curl_setopt_array($curlHandle, $options);
		curl_exec($curlHandle);
		$errt = curl_error($curlHandle);
		curl_close($curlHandle);
		fclose($fileHandle);
		//
		// if file is too small, high chances to be a response header dump
		if (@filesize($to_tried) < 128)
		{
			// so, let's erase it and fail
			unlink($to_tried);
			return false;
		}
		// let's fail if file is empty
		elseif (!empty($errt))
		{
			return false;
		}
		//
		// rename to the target dest name
		rename($to_tried, $destination);
		//
		return true;
	}

	private function fetchArchiveInfo()
	{
		if (empty($this->downloadedLocation))
		{
			return false;
		}
		//
		if (!file_exists($this->downloadedLocation))
		{
			return false;
		}
		//
		$this->archiveInfo = [
			'time_created' => date('Y-m-d\TH:i:s\Z', filectime($this->downloadedLocation)),
			'time_lastmod' => date('Y-m-d\TH:i:s\Z', filemtime($this->downloadedLocation)),
			'size' => filesize($this->downloadedLocation),
			'hash_sha1' => sha1_file($this->downloadedLocation),
			'hash_md5' => md5_file($this->downloadedLocation),
		];
		//
		return true;
	}

	/**
	 *	Initializes a new instance
	 *
	 *	@param	\Packinst\Package\GitPackage	$package = null
	 *	@return	self
	 */
	public function __construct(GitPackage $package = null)
	{
		$this->setPackage($package);
	}

	/**
	 *	Sets the package the downloader should work with.
	 *	Accepts either a GitPackage instance or a string in the
	 *	group-name/project-name format.
	 *
	 *	@param	string|\Packinst\Package\GitPackage	$packageDef
	 *	@return	self
	 */
	public function setPackage($packageDef)
	{
		if ($packageDef instanceof GitPackage)
		{
			$this->package = $packageDef;
		}
		elseif (is_string($packageDef))
		{
			$matches = [];
			//
			if (preg_match(self::PATTERN, $packageDef, $matches))
			{
				$this->package = new GithubPackage($matches[1], $matches[2]);
			}
		}
		//
		return $this;
	}

	/**
	 *	Gets the package the downloader is associated with.
	 *
	 *	@return	\Packinst\Package\GitPackage|null
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 *	Returns where the downloaded file lies.
	 *
	 *	@return	string|null
	 */
	public function getDownloadedLocation()
	{
		return $this->downloadedLocation;
	}

	/**
	 *	Generates a loader file with PHP code for the Collei Plat MVC
	 *	Framework. It requires basic info on the package.
	 *
	 *	@param	string|\Packinst\Package\GitPackage	$packageDef
	 *	@return	self
	 */
	public function writeLoaderFileTo(string $destination, array $extraInfo = [])
	{
		$info = $this->package->repositoryInfo;

		if (!$info)
		{
			$this->package->fetchRepositoryInfo();
			$info = $this->package->repositoryInfo;
		}

		$parts = explode('/', $info->full_name);

		// write the 'init.php' needed for Collei Plat framework
		$initCode = "<?php\r\n\r\n"
			. "/**\r\n *	Register plugin engine and version\r\n */\r\n"
			. "plat_plugin_register(["
			. "\r\n\t'plugin' => '" . ($info->full_name) . "',"
			. "\r\n\t'description' => '" . ($info->description ?? 'none') . "',"
			. "\r\n\t'version' => '" . ($info->pushed_at ?? 'none') . "',";
		//
		if ($dbi = $this->package->defaultBranchInfo)
		{
			$initCode .= "\r\n\t'branch_details' => ["
				. "\r\n\t\t'commit_sha' => '" . ($dbi->commit->sha ?? '') . "',"
				. "\r\n\t\t'commit_node' => '" . ($dbi->commit->node_id ?? '') . "',"
				. "\r\n\t\t'commit_author' => '" . ($dbi->commit->commit->author->name ?? '') . "',"
				. "\r\n\t\t'commit_author_date' => '" . ($dbi->commit->commit->author->date ?? '') . "',"
				. "\r\n\t\t'commit_committer' => '" . ($dbi->commit->commit->committer->name ?? '') . "',"
				. "\r\n\t\t'commit_committer_date' => '" . ($dbi->commit->commit->committer->date ?? '') . "',"
				. "\r\n\t],";
		}
		//
		if ($extraInfo['dependencies'] ?? false)
		{
			if (!empty($extraInfo['dependencies']))
			{
				$initCode .= "\r\n\t'dependencies' => [";
				foreach ($extraInfo['dependencies'] as $index => $value)
				{
					$initCode .= "\r\n\t\t'{$index}' => '{$value}',";
				}
				$initCode .= "\r\n\t],";
			}
		}
		//
		if (!empty($this->archiveInfo))
		{
			$initCode .= "\r\n\t'archive_info' => [";
			foreach ($this->archiveInfo as $index => $value)
			{
				$initCode .= "\r\n\t\t'{$index}' => '{$value}',";
			}
			$initCode .= "\r\n\t],";
		}
		//
		$initCode .= ""
			. "\r\n\t'classes_folder' => '" . ($extraInfo['classes_folder'] ?? 'none') . "',"
			. "\r\n]);\r\n\r\n";
		//
		file_put_contents($destination, $initCode);
		//
		return $this;
	}

	/**
	 *	Performs the download operation on the defined package to
	 *	the destination at $to. You can pass one or more $branches
	 *	to be searched upon the GIT repo, then it will return the first
	 *	successful one. If no branch is given, 'master' and 'main'
	 *	(in this order) will be tried instead.
	 *
	 *		$downloader->downloadTo($dest, 'master')
	 *		$downloader->downloadTo($dest, 'master', 'main', 'desenv')
	 *
	 *	@param	string	$to
	 *	@param	string	...$branches
	 *	@return	bool
	 */
	public function downloadTo(string $to, string ...$branches)
	{
		if (empty($this->package))
		{
			return false;
		}
		//
		if (empty($branches))
		{
			$branches = ['master','main'];
		}
		//
		set_time_limit(0);
		//
		foreach ($branches as $branch)
		{
			$uri = $this->package->getApiDownloadUri($branch);
			//
			if ($this->fetchCurlDownload($uri, $to))
			{
				$this->downloadedLocation = $to;
				$this->fetchArchiveInfo();
				//
				return true;
			}
		}
		//
		return false;
	}

}

