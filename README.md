# Module

[![Release](https://img.shields.io/packagist/v/icanboogie/module.svg)](https://packagist.org/packages/icanboogie/module)
[![Build Status](https://img.shields.io/github/workflow/status/ICanBoogie/Module/test)](https://github.com/ICanBoogie/Module/actions?query=workflow%3Atest)
[![Code Quality](https://img.shields.io/scrutinizer/g/ICanBoogie/Module.svg)](https://scrutinizer-ci.com/g/ICanBoogie/Module)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Module.svg)](https://coveralls.io/r/ICanBoogie/Module)
[![Packagist](https://img.shields.io/packagist/dt/icanboogie/module.svg)](https://packagist.org/packages/icanboogie/module)

This package provides support for modules to the framework [ICanBoogie](https://icanboogie.org).

A module is like a tiny application inside your application. It provides its own configurations,
routes, operations, views, templates… it contains everything it needs to execute a desired
functionality.





## Defining a module

It takes at least two files to define a module: a descriptor and another where the class used to
instantiate the module is defined. The descriptor is a `descriptor.php` file that must be located
at the root of the module directory. The class is usually located in the file `lib/module.php`.
The module directory is usually named with the identifier of the module.

The following directory structure demonstrates how a very basic `nodes` module could be defined:

    nodes
    ├─ lib
    │  └─ Module.php
    └─ descriptor.php

The following directory structure demonstrates a more advanced module:

    nodes
    ├─ config
    |  └─ <configuration files>
    ├─ lib
    |  ├─ Operation
    |  |  └─ <operation classes>
    |  └─ Module.php
    ├─ locale
    |  └─ <message catalogs>
    ├─ public
    |  └─ <public assets>
    ├─ tests
    |  └─ <tests>
    ├─ templates
    |  └─ <view templates>
    └─ descriptor.php





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

                'nid' => 'serial',
                'uid' => 'foreign',
                'site_id' => 'foreign',
                'native_id' => 'foreign',
                'constructor' => [ 'varchar', 64, 'indexed' => true ],
                'title' => 'varchar',
                'slug' => [ 'varchar', 80, 'indexed' => true ],
                'language' => [ 'varchar', 8, 'indexed' => true ],
                'created' => [ 'timestamp', 'default' => 'CURRENT_TIMESTAMP()' ],
                'modified' => 'timestamp',
                'is_online' => [ 'boolean', 'indexed' => true ]

            ]
        ]
    ],

    Descriptor::NS => __NAMESPACE__,
    Descriptor::PERMISSION => false,
    Descriptor::PERMISSIONS => [

        'modify belonging site'

    ],

    Descriptor::REQUIRES => [ 'sites', 'users' ],
    Descriptor::TITLE => 'Nodes'

];
```





#### Descriptor tags

Here are the tags (`Descriptor::<tag>`) that can be used to define the module's descriptor.

- `CATEGORY`: Defines the category for the module.
- `CLASSNAME`: Defines the PHP class of the module. Defaults to `<namespace>\Module`.
- `DESCRIPTION`: Defines a short description of the module.
- `INHERITS`: Defines the module that the module extends.
- `ID`: Defines the identifier of the module. Defaults to its directory name.
- `REQUIRES`: Defines the modules required, used to compute modules weight.
- `MODELS`: Defines the models of the module. Take a look at the [ActiveRecord package][] for
more information about ActiveRecords and models.
- `NS`: Defines the namespace of the module.
- `PERMISSION`: Defines the general permission required to use this module.
- `PERMISSIONS`: Defines module specific permissions.
- `TITLE`: Defines the title of the module.





### Operations

Module operations are defined in the "lib/Operation" directory. For example a `save`
operation would be a class named `SaveOperation` declared within the namespace `<module namespace>\Operation`,
located in a "SaveOperation.php" file.

Operations are considered inherited. If the `save` operation is requested on a _News_ module,
the framework tries to locate the best matching operation class, according to the modules it
extends:

- `..\News\Operation\SaveOperation`
- `..\Contents\Operation\SaveOperation`
- `..\Nodes\Operation\SaveOperation`





## Module collection

Modules are loaded through a module collection. The collection indexes modules and provide access to
their descriptors. The dependencies between the modules is respected and they are sorted
accordingly.

A module collection is represented by a [ModuleCollection][] instance, constructed from an array of
paths and an optional cache. The paths array defined where the modules are located, while the cache
is used to store and retrieve the collection index.

The following example demonstrates how a module collection can be created from two separate
module locations and the single location of a module, a cache is also provided:

```php
<?php

use ICanBoogie\Module\ModuleCollection;
use ICanBoogie\Storage\FileStorage;

$vars = new FileStorage(__DIR__ . '/repository/vars');

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

/* @var \ICanBoogie\Module\ModuleCollection $modules */

$node_module = $modules['nodes'];
```

`isset()` is used to check if a module is defined. The [ModuleNotDefined][] exception is thrown if
the module is not defined, and [ModuleConstructorMissing][] is thrown when the class used to
instantiate the module is missing.

```php
<?php

/* @var \ICanBoogie\Module\ModuleCollection $modules */

isset($modules['nodes']); // true
isset($modules['undefined_module']); // false
```





## Installing and uninstalling modules

Modules are installed using the `install()` method, and uninstalled using the
`uninstall()` method. The `is_installed()` method returns the installation state of the
module and also collects the reasons why the module is not installed.

```php
<?php

use ICanBoogie\ErrorCollection;

/* @var \ICanBoogie\Module\ModuleCollection $modules */

$nodes = $modules['nodes'];
$errors = new ErrorCollection;

if (!$nodes->is_installed($errors))
{
    #
    # $errors might contain messages about why the module is not installed
    #

    $errors->clear();

    if (!$nodes->install($errors))
    {
        #
        # $errors might contain the reasons why the module failed to install
        #
    }
}

$nodes->uninstall();
```

Modules can be installed all at once using a [ModuleCollection][] instance. The
[ModuleCollectionInstallFailed][] exception is thrown with all the errors and exceptions collected
in a [ErrorCollection][] instance if the installation fails.

```php
<?php

use ICanBoogie\Module\ModuleCollectionInstallFailed;

/* @var \ICanBoogie\Module\ModuleCollection $modules */

try
{
    $modules->install();
}
catch (ModuleCollectionInstallFailed $e)
{
    echo get_class($e->errors); // ICanBoogie\ErrorCollection
}
```





## Autoconfig

The package supports the _autoconfig_ feature of [ICanBoogie][] and provides the following:

- A lazy getter for the `ICanBoogie\Application::$modules` property, that returns a [ModuleCollection][]
instance configured to provide the modules of the application.
- A lazy getter for the `ICanBoogie\Application::$models` property, that returns a [ModelCollection][]
instance configured to provide the models defined by the modules.

```php
<?php

/* @var \ICanBoogie\Application $app */

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

/* @var \ICanBoogie\Application $app */

$nodes_model = $app->models['nodes'];
```

And if the [Nodes][] module was defining an `attachments` model:

```php
<?php

/* @var \ICanBoogie\Application $app */

$nodes_attachments_model = $app->models['nodes/attachments'];
```





## Enhanced routing

The package adds the following getters to [Controller][] instances:

- `module`: Returns the [Module][] instance of the module associated with a route.
- `model`: Returns the primary model of `module`.
- `records_fetcher`: Returns a [Fetcher][] instance associated with `model`.

The following method is also added:

- `fetch_records`: Fetches records matching specified conditions using `fetcher`.
- `fetch_record`: Fetches a record matching specified conditions using `fetcher`.

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

use ICanBoogie\Routing\Controller;

class ArticlesController extends Controller
{
    use Controller\ActionTrait;
    use \ICanBoogie\Binding\Module\ControllerBindings;
    use \ICanBoogie\View\ControllerBindings;

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





## Template resolver decorator

A [ModuleTemplateResolver][] instance is used to decorate the template resolver instance defined
when the `ICanBoogie\Render\BasicTemplateResolver::alter` event of class
[TemplateResolver\AlterEvent][] is fired, adding support for module defined templates. When the
_path part_ of the template name matches an activated module identifier, the template pathname is
resolved using the module and its parents.





## Event hooks

- `ICanBoogie\View\View::alter`: If the view renders a module's route, the "template" directory
of that module is added to the list of template locations.

- `routing.collect_routes:before`: Alter routes defined by modules by adding a `module` key that
holds the identifier of the module that defines the route.





----------





## Installation

```bash
composer require icanboogie/module
```





## Documentation

The package is documented as part of the [ICanBoogie][] framework
[documentation](https://icanboogie.org/docs/). You can generate the documentation for the package
and its dependencies with the `make doc` command. The documentation is generated in the `build/docs`
directory. [ApiGen](http://apigen.org/) is required. The directory can later be cleaned with the
`make clean` command.





## Testing

Run `make test-container` to create and log into the test container, then run `make test` to run the
test suite. Alternatively, run `make test-coverage` to run the test suite with test coverage. Open
`build/coverage/index.html` to see the breakdown of the code coverage.





## License

**icanboogie/module** is released under the [New BSD License](LICENSE).






[ICanBoogie]:                    https://icanboogie.org/
[ErrorCollection]:               https://icanboogie.org/api/errors/master/class-ICanBoogie.Errors.html
[Events]:                        https://icanboogie.org/api/event/master/class-ICanBoogie.Events.html
[Controller]:                    https://icanboogie.org/api/routing/master/class-ICanBoogie.Routing.Controller.html
[Fetcher]:                       https://icanboogie.org/api/facets/master/class-ICanBoogie.Facets.Fetcher.html
[Module]:                        https://icanboogie.org/api/module/master/class-ICanBoogie.Module.html
[ModelCollection]:               https://icanboogie.org/api/module/master/class-ICanBoogie.Module.ModelCollection.html
[ModuleCollection]:              https://icanboogie.org/api/module/master/class-ICanBoogie.Module.ModuleCollection.html
[ModuleNotDefined]:              https://icanboogie.org/api/module/master/class-ICanBoogie.Module.ModuleNotDefined.html
[ModuleCollectionInstallFailed]: https://icanboogie.org/api/module/master/class-ICanBoogie.Module.ModuleCollectionInstallFailed.html
[ModuleConstructorMissing]:      https://icanboogie.org/api/module/master/class-ICanBoogie.Module.ModuleConstructorMissing.html
[ModuleTemplateResolver]:        https://icanboogie.org/api/module/master/class-ICanBoogie.Module.ModuleTemplateResolver.html
[TemplateResolver\AlterEvent]:   https://icanboogie.org/api/module/master/class-ICanBoogie.Render.TemplateResolver.AlterEvent.html
[icanboogie/facets]:             https://github.com/ICanBoogie/Facets
[icanboogie/i18n]:               https://github.com/ICanBoogie/I18n
[ActiveRecord package]:          https://github.com/ICanBoogie/ActiveRecord
[Nodes]:                         https://github.com/Icybee/module-nodes
