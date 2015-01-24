# Module [![Build Status](https://travis-ci.org/ICanBoogie/Module.svg?branch=2.0)](http://travis-ci.org/ICanBoogie/Module)

This package provides support for modules to the [ICanBoogie](http://icanboogie.org) framework.

A module is like a tiny application inside your application. It provides its own configurations,
routes, operations, views, templates… it contains everything it needs to execute a desired
functionality.





## Defining a module

It takes at least two files to define a module: a descriptor and another where the class used to
instantiate the module is defined. The descriptor is a `descriptor.php` file that must be located
at the root of the module directory. The class is usually located in the file 'lib/module.php`.
The module directory is usually named with the identifier of the module.

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
	|_ templates
	|  |_ <view templates>
	|_ descriptor.php





### The descriptor

The descriptor is a very important file because it describes the module and its models. At the
very least the descriptor must define the title and the namespace of the module (yes namespace is
mandatory).

The following code is the descriptor of the [Nodes][] module. You'll notice that the module
is using the namespace `Icybee\Modules\Nodes`.

```php
<?php

namespace Icybee\Modules\Nodes;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module\Descriptor;

return [

	Descriptor::CATEGORY => 'contents',
	Descriptor::DESCRIPTION => 'Centralized node system base',
	Descriptor::MODELS => [

		'primary' => [

			Model::SCHEMA => [

				'fields' => [

					'nid' => 'serial',
					'uid' => 'foreign',
					'siteid' => 'foreign',
					'nativeid' => 'foreign',
					'constructor' => [ 'varchar', 64, 'indexed' => true ],
					'title' => 'varchar',
					'slug' => [ 'varchar', 80, 'indexed' => true ],
					'language' => [ 'varchar', 8, 'indexed' => true ],
					'created' => [ 'timestamp', 'default' => 'CURRENT_TIMESTAMP()' ],
					'modified' => 'timestamp',
					'is_online' => [ 'boolean', 'indexed' => true ]

				]
			]
		]
	],

	Descriptor::NS => __NAMESPACE__,
	Descriptor::PERMISSION => false,
	Descriptor::PERMISSIONS => [

		'modify belonging site'

	],

	Descriptor::REQUIRED => true,
	Descriptor::REQUIRES => [

		'sites' => '*',
		'users' => '*'

	],

	Descriptor::TITLE => 'Nodes',
	Descriptor::VERSION => '1.0'

];
```





#### Descriptor tags

Here are the tags (`Descriptor::<tab>`) that can be used to define the module's descriptor.

- `CATEGORY`: Defines the category for the module.
- `CLASSNAME`: Defines the PHP class of the module. Defaults to `<namespace>\Module`.
- `DESCRIPTION`: Defines a short description of the module.
- `INHERITS`: Defines the module that the module extends.
- `ID`: Defines the identifier of the module. Defaults to its directory name.
- `REQUIRED`: Defines that the module is required and cannot be disabled.
- `REQUIRES`: Defines the modules (and the versions) that the module requires.
- `MODELS`: Defines the models of the module. Take a look at the [ActiveRecord package][] for
more informations about ActiveRecords and models.
- `NS`: Defines the namespace of the module.
- `PERMISSION`: Defines the general permission required to use this module.
- `PERMISSIONS`: Defines module specific permissions.
- `TITLE`: Defines the title of the module.
- `VERSION`: Defines the version of the module.





### Operations

Module operations are usually defined in the "lib/operations" directory. For example a `save`
operation would be a class named `SaveOperation` declared within the namespace of the module,
located in a "save.php" file.

Operations are considered inherited. If the `save` operation is requested on a _News_ module,
the framework tries to locate the best matching operation class, according to the modules it
extends:

- `..\News\SaveOperation`
- `..\Contents\SaveOperation`
- `..\Nodes\SaveOperation`





## Module collection

Modules are accessed and managed through a module collection. The collection indexes modules and
the resources they provide. It is used to enabled/disable and load modules. The collection also
takes care of the dependencies between the modules and sorts them accordingly.

A module collection is represented by a [ModuleCollection][] instance, constructed from an array of paths
and an optional cache. The paths array defined where the modules are located, while
the cache is used to store and retrieve the collection index.

The following example demonstrates how a module collection can be created from two separate
module locations and the single location of a module, a cache is also provided:

```php
<?php

use ICanBoogie\ModuleCollection;
use ICanBoogie\Vars;

$vars = new Vars(__DIR__ . '/repository/vars');

$modules = new ModuleCollection([

	__DIR__ . '/vendor/icanboogie-modules',
	__DIR__ . '/protected/modules',
	__DIR__ . '/path/to/my/module'

], $vars);
```





### Accessing a module

Modules are accessed by using the collection as an array. The following example demonstrates how
the `nodes` module is accessed.

```php
<?php

$node_module = $modules['nodes'];
```

The [ModuleNotDefined][] exception is thrown if the module is not defined. The [ModuleIsDisabled][]
exception is thrown if the module is disabled. Finally, the [ModuleConstructorMissing][] is thrown
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





## (Un)Installing modules

Modules are installed using the `install()` method, and uninstalled using the
`uninstall()` method. The `is_installed()` method returns the installation state of the
module and also collects the reasons why the module is not installed.

```php
<?php

use ICanBoogie\Errors;

$nodes = $modules['nodes'];
$errors = new Errors;

if (!$nodes->is_installed($errors))
{
	#
	# $errors might contain messages about why the module is not installed
	#

	$errors->clean();

	if (!$nodes->install($errors))
	{
		#
		# $errors might contain the reasons why the module failed to install
		#
	}
}

$nodes->uninstall();
```

Enabled modules can be installed at once using a [ModuleCollection][] instance. Errors are exceptions are
collected in the provided [Errors][] instance.

```php
<?php

use ICanBoogie\Errors;

$errors = $modules->install(new Errors);
```





## Autoconfig

The package supports the _autoconfig_ feature of [ICanBoogie][] and provides the following:

- A lazy getter for the `ICanBoogie\Core::$modules` property, that returns a [ModuleCollection][] instance
configured to provide the modules of the application.
- A lazy getter for the `ICanBoogie\Core::$models` property, that returns a [Models][] instance
configured to provide the models defined by the modules.

```php
<?php

$app->modules['nodes'];
$app->models['nodes'];
```





## An enhanced model provider

The package provides an enhanced model provider, which extends the model provider that comes with
the [ActiveRecord package] to add support for models defined by modules.

For instance, using this provider you can access the primary model defined by the [Nodes][] module
as follows:

```php
<?php

$nodes_model = $app->models['nodes'];
```

And if the [Nodes][] module was defining an `attachments` model:

```php
<?php

$nodes_attachments_model = $app->models['nodes/attachments'];
```





## Enhanced routing

The package adds the following getters to [Controller][] instances:

- `module`: Returns the [Module][] instance of the module associated with a route.
- `model`: Returns the primary model of `module`.
- `records_fetcher`: Returns a [Fetcher][] instance associated with `model`.

The following method is also added:

- `fetch_records`: Fetches records matching specified conditions using `fetcher`.

The route needs to define a `module` property in order for these getters to work, which the
package does automatically for routes defined in the "config" directory of modules.

The following example demonstrates how a module can define a route, remember that the `module`
options is automatically added, so it doesn't need to be defined:

```php
<?php

// modules/articles/config/routes.php

return [

	'articles/show' => [
	
		'pattern' => '/<year:\d{4}>-<month:\d{2}>-:slug.html',
		'controller' => "ArticlesController#show"
	
	]

];
```

The following example demonstrates how the `fetch_records` method can be used to fetch records
matching some conditions, and how the `model` property can be used to obtain the primary model of
the module associated with a route:

```php
<?php

use ICanBoogie\Routing\ActionController;

class ArticlesController extends ActionController
{
	protected function any_index()
	{
		$this->view->content = $this->fetch_records([ 'limit' => 10 ] + $this->request->params);
	}

	protected function any_show($year, $month, $slug)
	{
		$this->view->content = $this->model->filter_by_year_and_month_and_slug($year, $month, $slug)->one;
	}
}
```

**Note:** `records_fetcher` and `fetch_records` require the [icanboogie\facets] package.





## Event hooks





### `ICanBoogie\Core::boot`

Boot enabled modules. Before the modules are actually booted up, their index is used to alter
the I18n load paths (if the [icanboogie/i18n][] package is available) and the config paths.
Note that prototypes are reseted and the [Events][] instance associated with the core revoked.





### `ICanBoogie\View\View::alter`

If the view renders a module's route, the "template" directory of that module is added to the list
of templates locations.





### `routing.collect_routes:before`

Alter routes defined by modules by adding a `module` key that holds the identifier of the
module that defines the route.





----------





## Requirement

The package requires PHP 5.4 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/):

```
$ composer require icanboogie/module
```

The following packages are required, you might want to check them out:

- [icanboogie/activerecord](https://github.com/ICanBoogie/ActiveRecord)
- [icanboogie/errors](https://github.com/ICanBoogie/Errors)





### Cloning the repository

The package is [available on GitHub](https://github.com/ICanBoogie/Module), its repository can be
cloned with the following command line:

	$ git clone https://github.com/ICanBoogie/Module.git





## Documentation

The package is documented as part of the [ICanBoogie](http://icanboogie.org/) framework
[documentation](http://icanboogie.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later by
cleaned with the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all dependencies required to run the suite. The directory can
later by cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://travis-ci.org/ICanBoogie/Module.svg?branch=2.0)](http://travis-ci.org/ICanBoogie/Module)





## License

This package is licensed under the New BSD License - See the [LICENSE](LICENSE) file for details.





[icanboogie/facets]: https://github.com/ICanBoogie/Facets
[icanboogie/i18n]: https://github.com/ICanBoogie/I18n
[ActiveRecord package]: https://github.com/ICanBoogie/ActiveRecord
[Controller]: http://icanboogie.org/docs/class-ICanBoogie.Routing.Controller.html
[Events]: http://icanboogie.org/docs/class-ICanBoogie.Events.html
[Errors]: http://icanboogie.org/docs/class-ICanBoogie.Errors.html
[Fetcher]: http://icanboogie.org/docs/class-ICanBoogie.ActiveRecord.Fetcher.html
[ICanBoogie]: http://icanboogie.org/
[Models]: http://icanboogie.org/docs/class-ICanBoogie.Models.html
[Module]: http://icanboogie.org/docs/class-ICanBoogie.Module.html
[ModuleCollection]: http://icanboogie.org/docs/class-ICanBoogie.ModuleCollection.html
[ModuleNotDefined]: http://icanboogie.org/docs/class-ICanBoogie.ModuleNotDefined.html
[ModuleIsDisabled]: http://icanboogie.org/docs/class-ICanBoogie.ModuleIsDisabled.html
[ModuleConstructorMissing]: http://icanboogie.org/docs/class-ICanBoogie.ModuleConstructorMissing.html
[Nodes]: https://github.com/Icybee/module-nodes
