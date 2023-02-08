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

/**
 * Module descriptor options.
 *
 * @package ICanBoogie\Module
 */
final class Descriptor
{
    /**
     * Key for the module's identifier.
     */
    public const ID = 'id';

    /**
     * Key for the module's class.
     */
    public const CLASSNAME = 'class';

    /**
     * Key for a parent module.
     */
    public const PARENT = 'parent';

    /**
     * Key for required modules.
     */
    public const REQUIRES = 'requires';

    /**
     * Key for the module's models.
     */
    public const MODELS = 'models';

    /**
     * Key for the module's weight.
     */
    public const WEIGHT = 'weight';

    /**
     * Key for the module's ancestors.
     */
    public const ANCESTORS = 'ancestors';

	/**
	 * Defines the category for the module.
	 *
	 * When modules are listed they are usually grouped by category. The category is also often
	 * used to create the main navigation menu of the admin interface.
	 *
	 * The category of the module is translated within the `module_category` scope.
	 */
	const CATEGORY = 'category';

	/**
	 * Defines a short description of what the module do.
	 */
	const DESCRIPTION = 'description';

	/**
	 * Defines extra values.
	 */
	const EXTRA = 'extra';

	/**
	 * Path to the module's directory.
	 *
	 * This tag is resolved when the module is indexed.
	 */
	const PATH = 'path';

	/**
	 * General permission of the module.
	 */
	const PERMISSION = 'permission';

	/**
	 * Defines the permissions added by the module.
	 */
	const PERMISSIONS = 'permissions';

	/**
	 * Defines the title of the module.
	 *
	 * The title of the module is translated within the `module_title` scope.
	 */
	const TITLE = 'title';

	private function __construct() {}
}
