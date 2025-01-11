<?php

namespace Test\ICanBoogie;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleProvider;
use ICanBoogie\PropertyNotWritable;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Acme\Article;
use Test\ICanBoogie\Acme\Node;

final class ModuleTest extends TestCase
{
    private Descriptor $node_descriptor;
    private Module $node_module;
    private Module $article_module;

    protected function setUp(): void
    {
        $provider = $this->createMock(ModuleProvider::class);

        $this->node_module = new Module(
            $this->node_descriptor = new Descriptor(
                id: 'nodes',
                class: Module::class,
                models: [ Node::class ]
            ),
            $provider
        );

        $this->article_module = new Module(
            new Descriptor(
                id: 'articles',
                class: Module::class,
                parent: $this->node_module->id,
                models: [ Article::class ]
            ),
            $provider
        );

        $provider->method('module_for_id')
            ->willReturnCallback(fn(string $id) => match ($id) {
                $this->node_module->id => $this->node_module,
                $this->article_module->id => $this->article_module,
                default => throw new LogicException()
            });
    }

    public function test_get_descriptor(): void
    {
        $this->assertSame($this->node_descriptor, $this->node_module->descriptor);
    }

    public function test_get_flat_id(): void
    {
        $provider = $this->createMock(ModuleProvider::class);

        $m = new Module(
            new Descriptor('name.space.to.id', Module::class),
            $provider
        );

        $this->assertEquals('name_space_to_id', $m->flat_id);
    }

    public function test_get_id(): void
    {
        $this->assertEquals($this->node_descriptor->id, $this->node_module->id);
    }

    public function test_get_model(): void
    {
        $this->assertInstanceOf(Model::class, $this->node_module->model);
    }

    public function test_get_parent(): void
    {
        $this->assertSame($this->node_module, $this->article_module->parent);
    }
}
