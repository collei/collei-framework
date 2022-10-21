<?php
namespace Collei\Http\Uploaders;

use Collei\Http\Request;
use Collei\Http\Uploaders\UploadedFile;
use Collei\Support\Collections\TypedCollection;

/**
 *	Encapsulates a request with file uploading
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-04-xx
 */
class FileUploadRequest extends Request
{
	/**
	 *	@var array $files
	 */
	private $files = [];

	/**
	 *	Collects info about uploaded files
	 *
	 *	@return	void
	 */
	private function getUploadedInfo()
	{
		foreach ($_FILES as $n => $uploaded)
		{
			if (is_array($uploaded['error'])) 
			{
				foreach ($_FILES[$n]['error'] as $k => $error)
				{
					if ($error == UPLOAD_ERR_OK)
					{
						$this->files[] = [
							'field_name' => $n,
							'field_index' =>  $k,
							'name' => $_FILES[$n]['name'][$k],
							'type' => $_FILES[$n]['type'][$k],
							'size' => $_FILES[$n]['size'][$k],
							'tmp_name' => $_FILES[$n]['tmp_name'][$k]
						];
					}
				}
			}
			else
			{
				foreach ($_FILES as $n => $uploaded)
				{
					$this->files[] = [
						'field_name' => $n,
						'name' => $uploaded['name'],
						'type' => $uploaded['type'],
						'size' => $uploaded['size'],
						'tmp_name' => $uploaded['tmp_name']
					];
				}
			}
		}
	}

	/**
	 *	Buils and initializes a new Uploader request
	 *
	 */
	protected function __construct()
	{
		parent::__construct();
		$this->getUploadedInfo();
	}

	/**
	 *	Returns how many files was uploaded
	 *
	 *	@return	int
	 */
	public function getUploadedCount()
	{
		return count($this->files);
	}

	/**
	 *	Returns if is there any file
	 *
	 *	@return	bool
	 */
	public function hasFiles()
	{
		return $this->hasUploadedFiles();
	}

	/**
	 *	Returns if some file was uploaded 
	 *
	 *	@return	bool
	 */
	public function hasUploadedFiles()
	{
		return $this->getUploadedCount() > 0;
	}

	/**
	 *	Returns the uploaded files, if any
	 *
	 *	@return	\Collei\Support\Collections\TypedCollection
	 */
	public function getUploadedFiles()
	{
		$files = [];

		foreach ($this->files as $file)
		{
			if (isset($file['field_index']))
			{
				$files[] = UploadedFile::make($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['field_name'], $file['field_index']);
			}
			else
			{
				$files[] = UploadedFile::make($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['field_name']);
			}
		}

		return TypedCollection::fromTypedArray($files, UploadedFile::class);
	}

	/**
	 *	Checks if is there any file on the current request or not
	 *
	 *	@static
	 *	@author doub1ejack <https://stackoverflow.com/users/263900/doub1ejack>
	 *	@since 2012-06-18 19:58 at <https://stackoverflow.com/questions/946418/how-to-check-whether-the-user-uploaded-a-file-in-php/11090136#11090136>	
	 *	@viewed 2021-11-13 15:22 GMT-3
	 *	@return	bool	true if at least one file is being submitted, false otherwise
	 */
	public static function hasFilesOnRequest()
	{
		// bail if there were no upload forms
		if (empty($_FILES))
		{
			return false;
		}

		// check for uploaded files
		foreach($_FILES as $field_name => $file)
		{
			if(!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name']))
			{
				// found one!
				return true;
			}
		}
		// return false if no files were found
		return false;
	}

}


