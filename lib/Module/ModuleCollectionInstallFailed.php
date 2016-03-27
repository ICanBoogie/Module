<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\ErrorCollection;
use ICanBoogie\HTTP\Status;

/**
 * Exception thrown when the installation of a module collection fails.
 *
 * @property-read ErrorCollection $errors
 */
class ModuleCollectionInstallFailed extends \Exception
{
	use AccessorTrait;

	/**
	 * @var ErrorCollection
	 */
	private $errors;

	/**
	 * @return ErrorCollection
	 */
	protected function get_errors()
	{
		return $this->errors;
	}

	/**
	 * @inheritdoc
	 *
	 * @param ErrorCollection $errors Module collection installation errors.
	 */
	public function __construct(ErrorCollection $errors, $message = null, $code = Status::INTERNAL_SERVER_ERROR, \Exception $previous = null)
	{
		$this->errors = $errors;

		if (!$message)
		{
			$message = $this->format_message($errors);
		}

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Formats exception message given errors.
	 *
	 * @param ErrorCollection $errors
	 *
	 * @return string
	 */
	protected function format_message(ErrorCollection $errors)
	{
		$message = "Module collection installation failed:\n";

		/* @var $error \ICanBoogie\Error */

		foreach ($errors as $module_id => $error)
		{
			$message .= "\n- $module_id: $error\n";
		}

		return $message;
	}
}
