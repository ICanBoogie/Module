# Module

[![Packagist](https://img.shields.io/packagist/v/icanboogie/module.svg)](https://packagist.org/packages/icanboogie/module)
[![Code Quality](https://img.shields.io/scrutinizer/g/ICanBoogie/Module.svg)](https://scrutinizer-ci.com/g/ICanBoogie/Module)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Module.svg)](https://coveralls.io/r/ICanBoogie/Module)
[![Downloads](https://img.shields.io/packagist/dt/icanboogie/module.svg)](https://packagist.org/packages/icanboogie/module)

This package provides support for modules to the framework [ICanBoogie](https://icanboogie.org).

A module is like a tiny application inside your application. It provides its own configurations,
routes, operations, views, templates… it contains everything it needs to execute a desired
functionality.



#### Installation

```bash
composer require icanboogie/module
```




## Defining a module

It takes at least two files to define a module: a configuration fragment and a module class. The configuration fragment is a `module.php` file located in the `config` directory. The class is usually located in the file `lib/Module.php`. The module directory is usually named with the identifier of the module.

The following directory structure demonstrates how a very basic `nodes` module could be defined:

    nodes
    ├─ config
    │  └─ module.php
    └─ lib
       └─ Module.php

The following directory structure demonstrates a more advanced module:

    nodes
    ├─ config
    │  └─ module.php
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
    └─ templates
       └─ <view templates>





### The configuration fragment

The configuration fragment describes the module and its models. At the very least the identifier and the class of the module must be defined. The modules configuration takes care of verifying module relationships integrity, computing module weights, and sorting modules according to their weight.

The following code could be a configuration fragment for a "nodes" module.

```php
<?php

namespace Acme\Nodes;

use ICanBoogie\Binding\Module\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
    ->add_module(
        id: 'nodes',
        class: Module::class
    );
```





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





## Working with modules

There's two ways to work with modules: a provider is good enough when all your need is load a module, a collection is better if you need to work on multiple modules such as installation.



### Accessing a module

You can get a module using a module provider:

```php
<?php

/* @var \ICanBoogie\Module\ModuleProvider $provider */

$nodes = $provider->module_for_id('nodes');
```

You can check a module is defined with the `has_module` method:

```php
<?php

/* @var \ICanBoogie\Module\ModuleProvider $provider */

$provider->has_module('nodes');
```

You can also iterate over the provider, and get modules:

```php
<?php

/* @var \ICanBoogie\Module\ModuleProvider $provider */

foreach ($provider as $module_id => $get) {
    $module = $get();

    // …
}
```



## Installing and uninstalling modules

Modules are installed using the `install()` method, and uninstalled using the `uninstall()` method. The `is_installed()` method returns the installation state of the module and also collects the reasons why the module is not installed.

```php
<?php

use ICanBoogie\ErrorCollection;
use ICanBoogie\Module;

/* @var Module $nodes */

$errors = new ErrorCollection();

if (!$nodes->is_installed($errors)) {
    #
    # $errors might contain messages about why the module is not installed
    #

    $errors->clear();

    if (!$nodes->install($errors)) {
        #
        # $errors might contain the reasons why the module failed to install
        #
    }
}

$nodes->uninstall();
```

Modules can be installed all at once using a [ModuleCollection][] instance. The [ModuleCollectionInstallFailed][] exception is thrown with all the errors and exceptions collected
in a [ErrorCollection][] instance if the installation fails.

```php
<?php

use ICanBoogie\Module\ModuleCollection;use ICanBoogie\Module\ModuleInstaller\ModuleInstallFailed;

/* @var ModuleCollection $modules */

try {
    $modules->install();
} catch (ModuleInstallFailed $e) {
    echo get_class($e->errors); // ICanBoogie\ErrorCollection
}
```





## Autoconfig

The package supports the _autoconfig_ feature of [ICanBoogie][].





## Template resolver decorator

A [ModuleTemplateResolver][] instance is used to decorate the template resolver instance defined
when the `ICanBoogie\Render\BasicTemplateResolver::alter` event of class
[TemplateResolver\AlterEvent][] is fired, adding support for module defined templates. When the
_path part_ of the template name matches an activated module identifier, the template pathname is
resolved using the module and its parents.





## Event hooks

- `routing.collect_routes:before`: Alter routes defined by modules by adding a `module` key that
holds the identifier of the module that defines the route.





----------



## Continuous Integration

The project is continuously tested by [GitHub actions](https://github.com/ICanBoogie/Module/actions).

[![Tests](https://github.com/ICanBoogie/Module/workflows/test/badge.svg?branch=master)](https://github.com/ICanBoogie/Module/actions?query=workflow%3Atest)
[![Static Analysis](https://github.com/ICanBoogie/Module/workflows/static-analysis/badge.svg?branch=master)](https://github.com/ICanBoogie/Module/actions?query=workflow%3Astatic-analysis)
[![Code Style](https://github.com/ICanBoogie/Module/workflows/code-style/badge.svg?branch=master)](https://github.com/ICanBoogie/Module/actions?query=workflow%3Acode-style)



## Code of Conduct

This project adheres to a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in
this project and its community, you are expected to uphold this code.



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.



## License

**icanboogie/module** is released under the [BSD-3-Clause](LICENSE).



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
