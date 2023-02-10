<?php

namespace ICanBoogie\Module;

use ICanBoogie\Module\ModuleInstaller\ModuleInstallFailed;

interface ModuleInstaller
{
    /**
     * Install all modules.
     *
     * @throws ModuleInstallFailed
     */
    public function install_all(): void;

    /**
     * Install one module
     *
     * @throws ModuleInstallFailed
     */
    public function install_one(string $id): void;
}
