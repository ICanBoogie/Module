<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\ModelNotDefined;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleProvider;
use RuntimeException;
use Throwable;

use function ICanBoogie\ActiveRecord\get_model;
use function is_string;

/**
 * A module of the framework.
 *
 * @property-read string $flat_id Underscored identifier.
 * @property-read string $id The identifier of the module, defined by {@link Descriptor::$id}.
 * @property-read Model $model The primary model of the module.
 * @property-read Module $parent The parent module, defined by {@link Descriptor::$parent}.
 * @property-read string $path The path to the module, defined by {@link Descriptor::$path}.
 * @property-read string $title The localized title of the module.
 * @property-read Application $app
 */
class Module extends Prototyped
{
    /*
     * PERMISSIONS:
     *
     * NONE: Well, you can't do anything
     *
     * ACCESS: You can access the module and view its records
     *
     * CREATE: You can create new records
     *
     * MAINTAIN: You can edit the records you created
     *
     * MANAGE: You can delete the records you created
     *
     * ADMINISTER: You have complete control over the module
     *
     */
    public const PERMISSION_NONE = 0;
    public const PERMISSION_ACCESS = 1;
    public const PERMISSION_CREATE = 2;
    public const PERMISSION_MAINTAIN = 3;
    public const PERMISSION_MANAGE = 4;
    public const PERMISSION_ADMINISTER = 5;

    /**
     * Defines the name of the operation used to save the records of the module.
     */
    public const OPERATION_SAVE = 'save';

    /**
     * Defines the name of the operation used to delete the records of the module.
     */
    public const OPERATION_DELETE = 'delete';

    /**
     * Returns the identifier of the module as defined by its descriptor.
     *
     * This method is the getter for the {@link $id} magic property.
     */
    protected function get_id(): string
    {
        return $this->descriptor->id;
    }

    /**
     * Returns the path of the module as defined by its descriptor.
     *
     * This method is the getter for the {@link $path} magic property.
     */
    protected function get_path(): string
    {
        /** @phpstan-ignore-next-line */
        return $this->descriptor->path;
    }

    public function __construct(
        public readonly Descriptor $descriptor,
        public readonly ModuleProvider $module_provider,
    ) {
    }

    /**
     * Returns the identifier of the module.
     */
    public function __toString(): string
    {
        return $this->id;
    }

    /**
     * Returns the _flat_ version of the module's identifier.
     *
     * This method is the getter for the {@link $flat_id} magic property.
     */
    protected function get_flat_id(): string
    {
        return strtr($this->id, [

            '.' => '_',
            '-' => '_'

        ]);
    }

    /**
     * Returns the primary model of the module.
     *
     * This is the getter for the {@link $model} magic property.
     */
    protected function get_model(): ActiveRecord\Model
    {
        return $this->model();
    }

    /**
     * Returns the module title, translated to the current language.
     *
     * @deprecated
     */
    protected function get_title(): string
    {
        $default = $this->descriptor->title ?? 'Undefined';

        /** @phpstan-ignore-next-line */
        return $this->app->translate($this->flat_id, [], [ 'scope' => 'module_title', 'default' => $default ]);
    }

    /**
     * Returns the parent module.
     */
    protected function get_parent(): ?Module
    {
        $parent = $this->descriptor->parent;

        return $parent ? $this->module_provider->module_for_id($parent) : null;
    }

    /**
     * Checks if the module is installed.
     *
     * @return bool|null `true` if the module is installed, `false` if the module (or parts of) is not installed, `null`
     * if the module has no installation.
     */
    public function is_installed(ErrorCollection $errors): ?bool
    {
        if (!$this->descriptor->models) {
            return null;
        }

        $rc = true;

        foreach ($this->descriptor->models as $id) {
            if (!$this->model($id)->is_installed()) {
                $errors->add($this->id, "The model %name is not installed.", [

                    'name' => $id

                ]);

                $rc = false;
            }
        }

        return $rc;
    }

    /**
     * Install the module.
     *
     * If the module has models they are installed.
     *
     * @return bool|null true if the module has successfully been installed, false if the
     * module (or parts of the module) fails to install or null if the module has
     * no installation process.
     */
    public function install(ErrorCollection $errors): ?bool
    {
        if (!$this->descriptor->models) {
            return null;
        }

        $rc = true;

        foreach ($this->descriptor->models as $id) {
            $model = $this->model($id);

            if ($model->is_installed()) {
                continue;
            }

            try {
                $model->install();
            } catch (Throwable $e) {
                $errors->add($this->id, "Unable to install model %model: !message", [

                    'model' => $id,
                    'message' => $e->getMessage()

                ]);

                $rc = false;
            }
        }

        return $rc;
    }

    /**
     * Uninstall the module.
     *
     * Basically it uninstall the models installed by the module.
     *
     * @return bool|null `true` if the module was successfully uninstalled. `false` if the module
     * (or parts of the module) failed to uninstall. `null` if there is no uninstall process.
     *
     * @throws Throwable
     */
    public function uninstall(): ?bool
    {
        if (!$this->descriptor->models) {
            return null;
        }

        $rc = true;

        foreach ($this->descriptor->models as $id) {
            $model = $this->model($id);

            if (!$model->is_installed()) {
                continue;
            }

            $model->uninstall();
        }

        return $rc;
    }

    /**
     * Get a model from the module.
     *
     * If the model has not been created yet, it is created on the fly.
     *
     * @throws ModelNotDefined when the model is not defined by the module.
     * @throws RuntimeException when the class of the model does not exist.
     */
    public function model(string $model_id = 'primary'): Model
    {
        if ($model_id === 'primary') {
            $model_id = current($this->descriptor->models);
        }

        assert(is_string($model_id));

        return get_model($model_id);
    }

    /**
     * Get a block.
     *
     * @return mixed Depends on the implementation. Should return a string or an object
     * implementing `__toString`.
     *
     * @throws RuntimeException if the block is not defined.
     */
    public function getBlock(string $name)
    {
        $args = func_get_args();

        array_shift($args);

        $callback = 'block_' . $name;

        if (!method_exists($this, $callback)) {
            throw new RuntimeException(
                format('The %method method is missing from the %module module to create block %type.', [

                    '%method' => $callback,
                    '%module' => $this->id,
                    '%type' => $name

                ])
            );
        }

        return $this->$callback(...$args);
    }
}
