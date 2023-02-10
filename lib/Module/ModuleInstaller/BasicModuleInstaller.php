<?php

namespace ICanBoogie\Module\ModuleInstaller;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Module\ModuleInstaller;
use ICanBoogie\Module\ModuleProvider;
use Throwable;

final class BasicModuleInstaller implements ModuleInstaller
{
    public function __construct(
        private readonly ModuleProvider $module_provider
    ) {
    }

    public function install_all(): void
    {
        $errors = new ErrorCollection();

        foreach ($this->installable_modules() as $id) {
            try {
                $this->module_provider->module_for_id($id)->install($errors);
            } catch (Throwable $e) {
                $errors[$id] = $e;
            }
        }

        if ($errors->count()) {
            throw new ModuleInstallFailed($errors);
        }
    }

    public function install_one(string $id): void
    {
        $errors = new ErrorCollection();

        try {
            $module = $this->module_provider->module_for_id($id);
            $module->install($errors);
        } catch (Throwable $e) {
            $errors->add($id, $e);
        }

        if ($errors->count()) {
            throw new ModuleInstallFailed($errors);
        }
    }

    /**
     * @return iterable<string>
     *     An iterable of module identifiers.
     */
    private function installable_modules(): iterable
    {
        $filter = new InstallableFilter($this->module_provider);

        foreach ($this->module_provider as $id => $get) {
            if ($filter($id)) {
                yield $id;
            }
        }
    }
}
