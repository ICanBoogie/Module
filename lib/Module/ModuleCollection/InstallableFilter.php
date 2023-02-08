<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\ModuleCollection;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleProvider;
use Throwable;

use function count;

/**
 * Filters installable module descriptors.
 */
class InstallableFilter
{
    public function __construct(
        private readonly ModuleProvider $provider
    ) {
    }

    /**
     * @param Descriptor $descriptor
     *
     * @return bool `true` if the module may be installed, `false` otherwise.
     */
    public function __invoke(Descriptor $descriptor): bool
    {
        $module = $this->provider->module_for_id($descriptor->id);
        $errors = new ErrorCollection;

        try {
            $is_installed = $module->is_installed($errors);

            if ($is_installed && !count($errors)) {
                return false;
            }
        } catch (Throwable) {
            # there was an error, the module might not be properly installed.
        }

        return true;
    }
}
