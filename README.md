# Module

This package provides support for modules to the [ICanBoogie](http://icanboogie.org) framework.

A module is like a tiny application inside a your application, like the application it provides
its own configurations, routes, operations, views, templates… it contains everything it needs to
execute a desired functionality.





## Defining a module

It take two files to define a module: a descriptor and another where the class used to instantiate
the module is defined. The descriptor is a `descriptor.php` file that must be located at the root
of the module directory. The class is usually located in the file 'lib/module.php`. The module
directory is usually named with the identifier of the module.

The following directory structure demonstrates how a very basic `nodes` module could be defined:

	nodes
	|_ lib
	|  |_ module.php
	|_ descriptor.php

The following directory structure demonstrates a more advanced module:

	nodes
	|_ config
	|  |_ <configuration files>
	|_ lib
	|  |_ activerecords
	|  |  |_ node.php
	|  |  |_ node.model.php
	|  |_ elements
	|  |  |_ <html elements>
	|  |_ operations
	|  |  |_ <operations handled by the module>
	|  |_ views
	|     |_ <views and providers>
	|_ locale
	|  |_ <message catalogs>
	|_ public
	|  |_ <public assets>
	|_ tests
	|  |_ <tests>
	|_ views
	|  |_ <view templates>
	|_ descriptor.php





### The descriptor

The descriptor is a very important file because it desribes the module and its models. At the
very least the descriptor must define the title and the namespace of the module.

The following code is the descriptor of the [Nodes][] module. You'll notice that the module
is using the namespace `Icybee\Modules\Nodes`.

```php
<?php

namespace Icybee\Modules\Nodes;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'contents',
	Module::T_DESCRIPTION => 'Centralized node system base',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::SCHEMA => array
			(
				'fields' => array
				(
					'nid' => 'serial',
					'uid' => 'foreign',
					'siteid' => 'foreign',
					'nativeid' => 'foreign',
					'constructor' => array('varchar', 64, 'indexed' => true),
					'title' => 'varchar',
					'slug' => array('varchar', 80, 'indexed' => true),
					'language' => array('varchar', 8, 'indexed' => true),
					'created' => array('timestamp', 'default' => 'CURRENT_TIMESTAMP()'),
					'modified' => 'timestamp',
					'is_online' => array('boolean', 'indexed' => true)
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_PERMISSION => false,
	Module::T_PERMISSIONS => array
	(
		'modify belonging site'
	),

	Module::T_REQUIRED => true,
	Module::T_REQUIRES => array
	(
		'sites' => '*',
		'users' => '*'
	),

	Module::T_TITLE => 'Nodes',
	Module::T_VERSION => '1.0'
);
```




## Module collection

Modules are accessed and managed through a module collection. The collection indexes modules and
the resources they provide. It is used to enabled/disable and load modules. The collection also
takes care of the dependencies between the modules and sorts them accordingly.

A module collection is represented by a [Modules][] instance, constructed from an array of paths
and an optionnal cache. The paths array defined where the modules are located, while
the cache is used to store and retrieve the collection index.

The following example demonstrates how a module collection can be created from two separate
module locations and the single location of a module, a cache is also provided:

```php
<?php

use ICanBoogie\Modules;
use ICanBoogie\Vars;

$vars = new Vars(__DIR__ . '/repository/vars');

$modules = new Modules(array(

	__DIR__ . '/vendor/icanboogie-modules',
	__DIR__ . '/protected/modules',
	__DIR__ . '/path/to/my/module'

), $vars);
```





### Accessing a module

Modules are accessed by using the collection as an array. The following example demonstrates how
the `nodes` module is accessed.

```php
<?php

$node_module = $modules['nodes'];
```

The [ModuleNotDefined][] exception is thrown if the module is not defined. The [ModuleIsDisabled][]
exception is thrown if the module is disabled. Finaly, the [ModuleConstructorMissing][] is thrown
when the class used to instantiate the module is missing.

Modules are considered _accessible_ when their descriptor is registered and they are not
disabled. `isset()` is used to check if a module is accessible:

```php
<?php

isset($modules['nodes']); // true

$modules->disable('nodes');

isset($modules['nodes']); // false
isset($modules['undefined_module']); // false
```






## Requirement

The package requires PHP 5.3 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",
	"require":
	{
		"icanboogie/module": "*"
	}
}
```





### Cloning the repository

The package is [available on GitHub](https://github.com/ICanBoogie/Module), its repository can be
cloned with the following command line:

	$ git clone git://github.com/ICanBoogie/Module.git





## Documentation

The package is documented as part of the [ICanBoogie](http://icanboogie.org/) framework
[documentation](http://icanboogie.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later by
cleaned with the `make clean` command.





## License

ICanBoogie/Module is licensed under the New BSD License - See the [LICENSE](https://raw.github.com/ICanBoogie/Module/master/LICENSE) file for details.





[Modules]: http://icanboogie.org/docs/class-ICanBoogie.Modules.html
[ModuleNotDefined]: http://icanboogie.org/docs/class-ICanBoogie.ModuleNotDefined.html
[ModuleIsDisabled]: http://icanboogie.org/docs/class-ICanBoogie.ModuleIsDisabled.html
[ModuleConstructorMissing]: http://icanboogie.org/docs/class-ICanBoogie.ModuleConstructorMissing.html
[Nodes]: https://github.com/Icybee/module-nodes