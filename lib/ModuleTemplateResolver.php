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

use ICanBoogie\Module;
use ICanBoogie\Render\TemplateResolver;
use ICanBoogie\Render\TemplateResolverTrait;

/**
 * Decorates a template resolver and adds support for module defined templates.
 *
 * Templates are inherited between modules.
 *
 * @package ICanBoogie\Module
 */
class ModuleTemplateResolver implements TemplateResolver
{
	use TemplateResolverTrait;

	/**
	 * @var TemplateResolver
	 */
	private $component;

	/**
	 * @var ModuleCollection
	 */
	private $modules;

	public function __construct(TemplateResolver $component, ModuleCollection $modules)
	{
		$this->component = $component;
		$this->modules = $modules;
	}

	/**
	 * @inheritdoc
	 */
	public function resolve($name, array $extensions, &$tried = [])
	{
		$template_pathname = $this->component->resolve($name, $extensions, $tried);

		if ($template_pathname)
		{
			return $template_pathname;
		}

		$modules = $this->modules;
		$module_id = dirname($name);

		if (empty($modules[$module_id]))
		{
			return null;
		}

		return $this->resolve_from_module($modules[$module_id], $name, $extensions, $tried);
	}

	/**
	 * Resolves a template from a module and its parents.
	 *
	 * @param Module $module
	 * @param string $name
	 * @param array $extensions
	 * @param array $tried
	 *
	 * @return string|null
	 */
	public function resolve_from_module(Module $module, $name, array $extensions, &$tried = [])
	{
		$paths = [];

		while ($module)
		{
			$paths[] = $module->path . 'templates' . DIRECTORY_SEPARATOR;

			$module = $module->parent;
		}

		return $this->resolve_path($this
			->resolve_tries($paths, $name, $extensions), $tried);
	}

	/**
	 * @inheritdoc
	 */
	public function add_path($path, $weight = 0)
	{
		$this->component->add_path($path, $weight);
	}

	/**
	 * @inheritdoc
	 */
	public function get_paths()
	{
		return $this->component->get_paths();
	}
}
