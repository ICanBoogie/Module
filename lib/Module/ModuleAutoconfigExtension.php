<?php

namespace ICanBoogie\Module;

use Composer\Package\RootPackageInterface;
use ICanBoogie\Autoconfig\Autoconfig;
use ICanBoogie\Autoconfig\AutoconfigExtensionAbstract;
use ICanBoogie\Autoconfig\ComposerExtra;

/**
 * Autoconfig extension to handle modules, their config and locale messages.
 */
class ModuleAutoconfigExtension extends AutoconfigExtensionAbstract
{
	const TYPE_MODULE = "icanboogie-module";

	/**
	 * @var array
	 */
	private $modules_directories;

	/**
	 * @inheritdoc
	 */
	public function synthesize(array &$autoconfig)
	{
		$generator = $this->generator;
		$modules_directories = $this->modules_directories = $this->collect_modules_directories();

		foreach ($modules_directories as $pathname)
		{
			if (is_dir("$pathname/config"))
			{
				$autoconfig[Autoconfig::CONFIG_PATH][] = [

					$generator->findShortestPathCode("$pathname/config"),
					Autoconfig::CONFIG_WEIGHT_MODULE

				];
			}

			if (is_dir("$pathname/locale"))
			{
				$autoconfig[Autoconfig::LOCALE_PATH][] = $generator
					->findShortestPathCode("$pathname/locale");
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function render()
	{
		$directories = array_map(function ($directory) {

			return "\t\t" . $this->generator->findShortestPathCode($directory) . ",\n";

		}, $this->modules_directories);

		$key = ModuleAutoconfig::MODULES;
		$directories = implode($directories);

		return <<<EOT
	'$key' => [

$directories
	],
EOT;
	}

	/**
	 * @return array
	 */
	private function collect_modules_directories()
	{
		$generator = $this->generator;
		$directories = [];

		foreach ($generator->packages as $pathname => $package)
		{
			if ($package->getType() != self::TYPE_MODULE)
			{
				continue;
			}

			$directories[] = $pathname;
		}

		foreach ($generator->packages as $package)
		{
			if (!$package instanceof RootPackageInterface)
			{
				continue;
			}

			$extra = $package->getExtra();

			if (empty($extra['icanboogie'][ComposerExtra::MODULES_PATH]))
			{
				continue;
			}

			$iterator = new \DirectoryIterator(getcwd() . DIRECTORY_SEPARATOR . $extra['icanboogie'][ComposerExtra::MODULES_PATH]);

			foreach ($iterator as $file)
			{
				if ($file->isDot() || $file->isFile())
				{
					continue;
				}

				$directories[] = $file->getPathname();
			}
		}

		return $directories;
	}
}
