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
use ICanBoogie\Errors;
use ICanBoogie\HTTP\Status;

/**
 * Exception thrown when the installation of a module collection fails.
 *
 * @property-read Errors $errors
 */
class ModuleCollectionInstallFailed extends \Exception
{
	use AccessorTrait;

	/**
	 * @var Errors
	 */
	private $errors;

	/**
	 * @return Errors
	 */
	protected function get_errors()
	{
		return $this->errors;
	}

	/**
	 * @inheritdoc
	 *
	 * @param Errors $errors Module collection installation errors.
	 */
	public function __construct(Errors $errors, $message = null, $code = Status::INTERNAL_SERVER_ERROR, \Exception $previous = null)
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
	 * @param Errors $errors
	 *
	 * @return string
	 */
	protected function format_message(Errors $errors)
	{
		$message = "Module collection installation failed:\n";

		foreach ($errors as $module_id => $m)
		{
			$message .= "\n- $module_id: $m\n";
		}

		return $message;
	}
}
