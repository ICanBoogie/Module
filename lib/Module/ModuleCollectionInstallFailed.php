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

use Exception;
use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\ErrorCollection;
use Throwable;

/**
 * Exception thrown when the installation of a module collection fails.
 *
 * @property-read ErrorCollection $errors
 */
final class ModuleCollectionInstallFailed extends Exception
{
    /**
     * @uses get_errors
     */
    use AccessorTrait;

    /**
     * @var ErrorCollection
     */
    private $errors;

    private function get_errors(): ErrorCollection
    {
        return $this->errors;
    }

    /**
     * @inheritdoc
     *
     * @param ErrorCollection $errors Module collection installation errors.
     */
    public function __construct(ErrorCollection $errors, string $message = null, Throwable $previous = null)
    {
        $this->errors = $errors;

        if (!$message) {
            $message = $this->format_message($errors);
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * Formats exception message given errors.
     */
    protected function format_message(ErrorCollection $errors): string
    {
        $message = "Module collection installation failed:\n";

        foreach ($errors as $module_id => $error) {
            $message .= "\n- $module_id: $error\n";
        }

        return $message;
    }
}
