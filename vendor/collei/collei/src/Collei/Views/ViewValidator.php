<?php
namespace Collei\Views;

use Collei\App\App;
use Collei\Views\ConstructBase;
use Collei\Views\ViewRenderer;
use Collei\Views\ColleiViewException;
use Collei\Utils\Files\TextFile;
use Collei\Utils\Arr;
use Collei\Utils\Str;
use ParseError;
use Throwable;

/**
 *	Embodies the view code validator methods
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-05-09
 */
class ViewValidator
{
	/**
	 *	@var string $sourceName
	 */
	private $sourceName = '';

	/**
	 *	@var \Collei\Utils\Files\TextFile $sourceFile
	 */
	private $sourceFile = null;

	/**
	 *	@var array $errors
	 */
	private $errors = [];

	/**
	 *	Joins error records into the internal error stream.
	 *
	 *	@param	array	$errors
	 *	@return void
	 */
	private function addErrors(array $errors)
	{
		if (empty($this->errors)) {
			$this->errors = $errors;
		} else {
			$this->errors = array_merge($this->errors, $errors);
		}
	}

	/**
	 *	Clears the internal error stream.
	 *
	 *	@return void
	 */
	private function clearErrors()
	{
		$this->errors = [];
	}

	/**
	 *	Retrieves the snippets that has the 'incomplete' component
	 *
	 *	@return array
	 */
	private function obtainStatements()
	{
		$snips = ConstructBase::getSnippetSets();
		$statements = [];
		//
		foreach ($snips as $snip) {
			if (isset($snip['incomplete'])) {
				$statements[] = $snip;
			}
		}
		//
		return $statements;
	}

	/**
	 *	Retrieves the snippets that has the 'validator' component
	 *
	 *	@return array
	 */
	private function obtainValidators()
	{
		$snips = ConstructBase::getSnippetSets();
		$validators = [];
		//
		foreach ($snips as $snip) {
			if (isset($snip['validator'])) {
				$validators[] = $snip;
			}
		}
		//
		return $validators;
	}

	/**
	 *	Returns the occurrences specified by $regex from the $source.
	 *	Returns false if none is found.
	 *
	 *	@param	string	$regex	the regex formula
	 *	@param	string	$source	the source code
	 *	@return	array|bool
	 */
	private function getOccurrences(string $regex, string $source)
	{
		$occurrences = [];
		$count = preg_match_all(
			('#' . $regex . '#'),
			$source,
			$occurrences,
			PREG_SET_ORDER | PREG_OFFSET_CAPTURE
		);
		//
		if ($count > 1) {
			return $occurrences;
		}
		//
		return false;
	}

	/**
	 *	Builds a test string around the code must be tested.
	 *
	 *	@param	array	$stmt
	 *	@param	array	$occur
	 *	@return	string
	 */
	private function createTest(array $stmt, array $occur)
	{
		$code = trim($occur[1][0]);
		//
		if ($stmt['name'] == 'foreach') {
			return 'foreach ' . $code . ' {} ';
		}
		//
		if (empty($code)) {
			$code = '()';
		} elseif (!Str::isClosed($code, '()')) {
			$code = '(' . $code . ')';
		}
		//
		return DUMMY_FUNCTION_NAME . $code . ';';
	}

	/**
	 *	Briefly verify basic Vis syntatics
	 *
	 *	@return	bool
	 */
	private function checkSyntatics()
	{
		$statements = $this->obtainStatements();
		$source = $this->sourceFile->getText();
		$results = [];
		//
		/*
		 *	let's turn off display of warnings and notices
		 *	- everything else is catchable errors being processed
		 *	in order to provide feasible feedback to the devs
		 *	as closer as possible to the troubled point.
		 */
		$previous_er = error_reporting((E_ALL ^ E_WARNING) ^ E_NOTICE);
		//
		foreach ($statements as $stmt) {
			$occurrences = $this->getOccurrences(
				$stmt['incomplete'], $source
			);
			//
			if ($occurrences) foreach ($occurrences as $occur) {
				$test = $this->createTest($stmt, $occur);
				//
				try {
					eval($test);
				} catch (Throwable $th) {
					// syntax errors only to be taken in account.
					// other issues can be only caught in runtime.
					if ($th instanceof ParseError) {
						$results[] = [
							'code' => $occur[0][0],
							'file' => $this->sourceName,
							'line' => Str::countLines($source, $occur[0][1]),
							'description' => $th->getMessage()
						];
					}
				}
			}
		}
		//
		//	restores previous settings
		error_reporting($previous_er);
		//
		$this->addErrors($results);
		//
		return empty($results);
	}

	/**
	 *	Validates the $match with the rules embodied into $validator
	 *	and returns the validation result.
	 *
	 *	@param	array	$validator	the code validator
	 *	@param	array	$match		the match related to the code
	 *	@return	bool
	 */
	private function validateMatch(array $validator, array $match)
	{
		$keys = ['closure','arg-indexes'];
		//
		if (Arr::hasKeys($validator, ...$keys)) {
			$clos = $validator['closure'];
			$args = [];
			//
			foreach ($validator['arg-indexes'] as $idx) {
				$args[] = Str::unquote($match[$idx][0] ?? '');
			}
			//
			return $clos(...$args);
		}
		//
		return true;
	}

	/**
	 *	Briefly run basic Vis snippet validators
	 *
	 *	@return	bool
	 */
	private function runValidators()
	{
		$validators = $this->obtainValidators();
		$source = $this->sourceFile->getText();
		$results = [];
		//
		foreach ($validators as $validator) {
			$occurrences = $this->getOccurrences(
				$validator['regex'], $source
			);
			//
			if ($occurrences) {
				foreach ($occurrences as $occur) {
					if (
						!$this->validateMatch($validator['validator'], $occur)
					) {
						$results[] = [
							'code' => $occur[0][0],
							'file' => $this->sourceName,
							'line' => Str::countLines($source, $occur[0][1]),
							'description' => $validator['validator']['reason']
						];
					}
				}
			}
		}
		//
		$this->addErrors($results);
		//
		return empty($results);
	}

	/**
	 *	Instance initialization with optional source loading
	 *
	 *	@param	string	$fileName = null
	 *	@return static
	 */
	public function __construct(string $fileName = null)
	{
		$this->load($fileName);
	}

	/**
	 *	Loads the source form the specified file, if any.
	 *
	 *	@param	string	$fileName = null
	 *	@return bool
	 */
	public function load(string $fileName = null)
	{
		if (!empty($fileName)) {
			$this->sourceName = $fileName;
			//
			if (!($this->sourceFile = new TextFile())->loadFrom($fileName)) {
				$this->sourceFile = null;
				//
				return false;
			}
			//
			return true;
		}
		//
		return false;
	}

	/**
	 *	Runs the check subroutines and returns boolean feedback
	 *
	 *	@return bool
	 */
	public function verify()
	{
		if (empty($this->sourceFile)) {
			$this->errors[] = [
				'error' => 'File not found.',
				'file' => $this->sourceName,
			];
			//
			return false;
		}
		//
		$this->clearErrors();
		//
		return $this->checkSyntatics() || $this->runValidators();
	}

	/**
	 *	Checks for errors in the internal error stream
	 *
	 *	@return bool
	 */
	public function hasErrors()
	{
		return !empty($this->errors);
	}

	/**
	 *	Returns all errors (if any) contained in the internal error stream.
	 *
	 *	@return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 *	Executes validation of the specified file and returns errors (if any).
	 *
	 *	@static
	 *	@param	string	$fileName
	 *	@return array
	 */
	public static function validate(string $fileName)
	{
		$that = new self($fileName);
		$that->verify();
		//
		return $that->getErrors();
	}

	/**
	 *	Builds a new instance of the validator and, optionally, verifies it.
	 *
	 *	@static
	 *	@param	string	$fileName
	 *	@param	bool	$verifyIt = true
	 *	@return void
	 */
	public static function for(string $fileName, bool $verifyIt = true)
	{
		$that = new self($fileName);
		//
		if ($verifyIt) {
			$that->verify();
		}
		//
		return $that;
	} 

}


