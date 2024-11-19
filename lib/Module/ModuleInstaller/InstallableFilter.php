<?php

namespace ICanBoogie\Module\ModuleInstaller;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Module\ModuleProvider;
use Throwable;

use function count;

/**
 * Filters installable module descriptors.
 */
final readonly class InstallableFilter
{
    public function __construct(
        private ModuleProvider $provider
    ) {
    }

    /**
     * @return bool `true` if the module may be installed, `false` otherwise.
     */
    public function __invoke(string $module_id): bool
    {
        $module = $this->provider->module_for_id($module_id);
        $errors = new ErrorCollection();

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
