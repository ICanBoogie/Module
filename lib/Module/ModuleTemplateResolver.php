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

use ICanBoogie\Binding\Module\Config;
use ICanBoogie\Render\TemplateResolver;
use ICanBoogie\Render\TemplateResolverTrait;

/**
 * Decorates a template resolver and adds support for module defined templates.
 *
 * Templates are inherited from parents.
 */
final class ModuleTemplateResolver implements TemplateResolver
{
    use TemplateResolverTrait;

    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(string $name, array $extensions, array &$tried = []): ?string
    {
        $descriptors = $this->config->descriptors;
        $module_id = $this->resolve_module_id($name);

        if (!$module_id || empty($descriptors[$module_id])) {
            return null;
        }

        return $this->resolve_from_module($descriptors, $module_id, $name, $extensions, $tried);
    }

    /**
     * Resolves module identifier form a template name.
     *
     * @return string|null The module identifier or `null` if it cannot be determined.
     */
    private function resolve_module_id(string $name): ?string
    {
        if (!preg_match('#^([^/]+)#', $name, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Resolves a template from a module and its parents.
     *
     * @param array<string, Descriptor> $descriptors
     * @param string[] $extensions
     * @param string[] $tried
     */
    private function resolve_from_module(
        array $descriptors,
        string $module_id,
        string $name,
        array $extensions,
        array &$tried = []
    ): ?string {
        $paths = [];
        $name = substr($name, strlen($module_id) + 1);

        while ($module_id) {
            $descriptor = $descriptors[$module_id];

            if (!$descriptor->path) {
                continue;
            }

            $paths[] = $descriptor->path . 'templates' . DIRECTORY_SEPARATOR;

            $module_id = $descriptor->parent;
        }

        $tries = $this->resolve_tries($paths, $name, $extensions);

        return $this->resolve_path($tries, $tried);
    }
}
