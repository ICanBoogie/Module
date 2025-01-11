<?php

namespace ICanBoogie;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\StaticModelProvider;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleProvider;
use RuntimeException;
use Throwable;

use function is_string;

/**
 * A module of the framework.
 */
class Module
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
    public const int PERMISSION_NONE = 0;
    public const int PERMISSION_ACCESS = 1;
    public const int PERMISSION_CREATE = 2;
    public const int PERMISSION_MAINTAIN = 3;
    public const int PERMISSION_MANAGE = 4;
    public const int PERMISSION_ADMINISTER = 5;

    /**
     * Defines the name of the operation used to save the records of the module.
     */
    public const string OPERATION_SAVE = 'save';

    /**
     * Defines the name of the operation used to delete the records of the module.
     */
    public const string OPERATION_DELETE = 'delete';

    /**
     * The identifier of the module as defined by its descriptor.
     */
    public string $id {
        get => $this->descriptor->id;
    }

    /**
     * The path of the module as defined by its descriptor.
     */
    public string $path
        {
            /** @phpstan-ignore-next-line */
            get => $this->descriptor->path;
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
     * The _flat_ version of the module's identifier.
     */
    public string $flat_id {
        get => strtr($this->id, [

            '.' => '_',
            '-' => '_',

        ]);
    }

    /**
     * The primary model of the module.
     */
    public ActiveRecord\Model $model {
        get => $this->model();
    }

    /**
     * The parent module, if any.
     */
    public ?Module $parent {
        get {
            $parent = $this->descriptor->parent;

            return $parent ? $this->module_provider->module_for_id($parent) : null;
        }
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

                    'name' => $id,

                ]);

                $rc = false;
            }
        }

        return $rc;
    }

    /**
     * Install the module.
     *
     * If the module has any model, they're installed as well.
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
                    'message' => $e->getMessage(),

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
     * @throws RuntimeException when the class of the model doesn't exist.
     */
    public function model(string $model_id = 'primary'): Model // @phpstan-ignore-line
    {
        if ($model_id === 'primary') {
            $model_id = current($this->descriptor->models);
        }

        assert(is_string($model_id));

        return StaticModelProvider::model_for_record($model_id); // @phpstan-ignore-line
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
                    '%type' => $name,

                ]),
            );
        }

        return $this->$callback(...$args);
    }
}
