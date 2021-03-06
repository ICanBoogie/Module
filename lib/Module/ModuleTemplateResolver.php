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
use ICanBoogie\Render\TemplateResolverDecorator;
use ICanBoogie\Render\TemplateResolverDecoratorTrait;
use ICanBoogie\Render\TemplateResolverTrait;

/**
 * Decorates a template resolver and adds support for module defined templates.
 *
 * Templates are inherited between modules.
 */
class ModuleTemplateResolver implements TemplateResolverDecorator
{
	use TemplateResolverTrait;
	use TemplateResolverDecoratorTrait;

	/**
	 * @var ModuleCollection
	 */
	private $modules;

	/**
	 * @param TemplateResolver $template_resolver
	 * @param ModuleCollection $modules
	 */
	public function __construct(TemplateResolver $template_resolver, ModuleCollection $modules)
	{
		$this->template_resolver = $template_resolver;
		$this->modules = $modules;
	}

	/**
	 * @inheritdoc
	 */
	public function resolve($name, array $extensions, &$tried = [])
	{
		$template_pathname = $this->template_resolver->resolve($name, $extensions, $tried);

		if ($template_pathname)
		{
			return $template_pathname;
		}

		$modules = $this->modules;
		$module_id = $this->resolve_module_id($name);

		if (empty($modules[$module_id]))
		{
			return null;
		}

		return $this->resolve_from_module($modules[$module_id], $name, $extensions, $tried);
	}

	/**
	 * Resolves module identifier form a template name.
	 *
	 * @param string $name
	 *
	 * @return string|null The module identifier or `null` if it cannot be determined.
	 */
	protected function resolve_module_id($name)
	{
		if (!preg_match('#^([^/]+)#', $name, $matches))
		{
			return null;
		}

		return $matches[1];
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
	protected function resolve_from_module(Module $module, $name, array $extensions, &$tried = [])
	{
		$paths = [];
		$name = substr($name, strlen($module->id) + 1);

		while ($module)
		{
			$paths[] = $module->path . 'templates' . DIRECTORY_SEPARATOR;

			$module = $module->parent;
		}

		return $this->resolve_path($this
			->resolve_tries($paths, $name, $extensions), $tried);
	}
}
