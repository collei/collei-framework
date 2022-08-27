<?php

namespace Packinst\Package;

/**
 *	@author	alarido.su@gmail.com
 *	@since	2022-06-19
 *
 *	General methods for Git packages
 *
 */
interface GitPackage 
{
	/**
	 *	Retrieves meta-info on the repository.
	 *
	 *	@return	self
	 */
	public function fetchRepositoryInfo();

	/**
	 *	Returns whether the repo was found or not
	 *
	 *	@return	bool
	 */
	public function repositoryExists();

	/**
	 *	Returns the full package name
	 *
	 *	@return	string
	 */
	public function getName();

	/**
	 *	Returns the package vendor name
	 *
	 *	@return	string
	 */
	public function getVendor();

	/**
	 *	Returns the package project name
	 *
	 *	@return	string
	 */
	public function getProject();

	/**
	 *	Returns an API URI for the package
	 *
	 *	@return	string
	 */
	public function getApiInfoUri();

	/**
	 *	Returns an API URI for the info on the related branch
	 *
	 *	@param	string	$branch = null
	 *	@return	string
	 */
	public function getApiBranchInfoUri(string $branch = null);

	/**
	 *	Returns an API URI for the package
	 *
	 *	@param	string	$branch = null
	 *	@return	string
	 */
	public function getApiDownloadUri(string $branch = null);

	/**
	 *	Returns an user-browseable URI for the package
	 *
	 *	@param	string	$branch = null
	 *	@return	string
	 */
	public function getBrowserUri(string $branch = null);

}


