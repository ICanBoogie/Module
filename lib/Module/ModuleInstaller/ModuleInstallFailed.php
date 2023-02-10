<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\ModuleInstaller;

use Exception;
use ICanBoogie\ErrorCollection;
use Throwable;

/**
 * Exception thrown when the installation of a module collection fails.
 */
final class ModuleInstallFailed extends Exception
{
    /**
     * @inheritdoc
     *
     * @param ErrorCollection $errors
     *     Where _key_ is a module identifier.
     */
    public function __construct(
        public readonly ErrorCollection $errors,
        string $message = null,
        Throwable $previous = null
    ) {
        $message ??= $this->format_message($errors);

        parent::__construct($message, previous: $previous);
    }

    /**
     * Formats exception message given errors.
     */
    private function format_message(ErrorCollection $errors): string
    {
        $message = "Module installation failed:\n";

        foreach ($errors as $module_id => $error) {
            $message .= "\n- $module_id: $error\n";
        }

        return $message;
    }
}
