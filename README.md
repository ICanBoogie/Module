# Module

This package provides support for modules to the [ICanBoogie](http://icanboogie.org) framework.

A module is like a tiny application inside a your application, like the application it provides
its own configuration, routes, operations, views, templates… it contains everything it needs to
execute a desired functionality.





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