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

final class Descriptor
{
	/**
	 * Defines the category for the module.
	 *
	 * When modules are listed they are usually grouped by category. The category is also often
	 * used to create the main navigation menu of the admin interface.
	 *
	 * The category of the module is translated within the `module_category` scope.
	 *
	 * @var string
	 */
	const CATEGORY = 'category';

	/**
	 * Defines the PHP class of the module.
	 *
	 * If the class is not defined it is resolved during indexing using the {@link NS}
	 * tag and the following pattern : `<namespace>\Module`.
	 *
	 * @var string
	 */
	const CLASSNAME = 'class';

	/**
	 * Defines a short description of what the module do.
	 *
	 * @var string
	 */
	const DESCRIPTION = 'description';

	/**
	 * Defines the state of the module.
	 *
	 * @var bool
	 */
	const DISABLED = 'disabled';

	/**
	 * Defines extra values.
	 *
	 * @var array
	 */
	const EXTRA = 'extra';

	/**
	 * Defines the parent module the module inherits from.
	 *
	 * @var string|\ICanBoogie\Module
	 */
	const INHERITS = 'extends'; // TODO-20101017: change to 'inherits' once the transition is over

	/**
	 * Defines the identifier of the module.
	 *
	 * If the identifier is not defined the name of the module directory is used instead.
	 *
	 * @var string
	 */
	const ID = 'id';

	/**
	 * Defines the state of the module.
	 *
	 * Required modules are always enabled.
	 *
	 * @var bool
	 */
	const REQUIRED = 'required';

	/**
	 * Defines the modules that the module requires.
	 *
	 * The required modules are defined using an array where each key/value pair is the identifier
	 * of the module and the minimum version required.
	 *
	 * @var array[string]string
	 */
	const REQUIRES = 'requires';

	/**
	 * Defines the models of the module.
	 *
	 * @var array[string]array|string
	 */
	const MODELS = 'models';

	/**
	 * Defines the namespace of the module.
	 *
	 * This attribute must be defined at construct time.
	 *
	 * @var string
	 */
	const NS = 'namespace';

	/**
	 * Path to the module's directory.
	 *
	 * This tag is resolved when the module is indexed.
	 *
	 * @var string
	 */
	const PATH = 'path';

	/**
	 * General permission of the module.
	 *
	 * @var string|int
	 */
	const PERMISSION = 'permission';

	/**
	 * Defines the permissions added by the module.
	 *
	 * @var array[]string
	 */
	const PERMISSIONS = 'permissions';

	/**
	 * Defines the title of the module.
	 *
	 * The title of the module is translated within the `module_title` scope.
	 *
	 * @var string
	 */
	const TITLE = 'title';

	/**
	 * Defines the version (and revision) of the module.
	 *
	 * @var string
	 */
	const VERSION = 'version';

	/**
	 * Defines the weight of the module.
	 *
	 * The weight of the module is resolved during modules indexing according to the
	 * {@link EXTENDS} and {@link REQUIRES} tags.
	 *
	 * @var int
	 */
	const WEIGHT = 'weight';

	static public function normalize(array $descriptor)
	{
		return $descriptor + [

			Descriptor::CATEGORY => null,
			Descriptor::CLASSNAME => $descriptor[Descriptor::NS] . '\Module',
			Descriptor::DESCRIPTION => null,
			Descriptor::DISABLED => false,
			Descriptor::EXTRA => [],
			Descriptor::INHERITS => null,
			Descriptor::ID => null,
			Descriptor::MODELS => [],
			Descriptor::PATH => null,
			Descriptor::PERMISSION => null,
			Descriptor::PERMISSIONS => [],
			Descriptor::REQUIRED => false,
			Descriptor::REQUIRES => [],
			Descriptor::VERSION => 'dev',
			Descriptor::WEIGHT => 0

		];
	}

	private function __construct() {}
}
