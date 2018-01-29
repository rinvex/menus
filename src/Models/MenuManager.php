<?php

declare(strict_types=1);

namespace Rinvex\Menus\Models;

use Closure;
use Countable;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Rinvex\Menus\Models\MenuGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;

class MenuManager implements Countable
{
    /**
     * The menus collection.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $menus;

    /**
     * The view factory.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $viewFactory;

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * The menu factory.
     *
     * @var \Rinvex\Menus\Models\MenuGenerator
     */
    protected $generator;

    /**
     * The menu callbacks.
     *
     * @var array
     */
    protected $callbacks = [];

    /**
     * The constructor.
     *
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     * @param \Illuminate\Routing\Router         $router
     */
    public function __construct(ViewFactory $viewFactory, Router $router)
    {
        $this->viewFactory = $viewFactory;
        $this->callbacks = collect();
        $this->menus = collect();
        $this->router = $router;
    }

    /**
     * Check if the menu exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name): bool
    {
        return $this->menus->has($name);
    }

    /**
     * Get instance of the given menu if exists.
     *
     * @param string $name
     *
     * @return \Rinvex\Menus\Models\MenuGenerator|null
     */
    public function instance($name): ?MenuGenerator
    {
        return $this->menus->get($name);
    }

    /**
     * Register a menu.
     *
     * @param string   $name
     * @param \Closure $callback
     *
     * @return void
     */
    public function register($name, Closure $callback): void
    {
        if ($this->has($name)) {
            $this->callbacks = $this->callbacks->put($name, $this->callbacks->get($name, collect())->push($callback));
        } else {
            $builder = new MenuGenerator();

            $builder->setViewFactory($this->viewFactory);

            $this->menus->put($name, $builder);

            $this->callbacks = $this->callbacks->put($name, $this->callbacks->get($name, collect())->push($callback));
        }
    }

    /**
     * Render the menu tag by given name.
     *
     * @param string $name
     * @param string $presenter
     * @param array  $bindings
     * @param bool   $specialSidebar
     *
     * @return string|null
     */
    public function render(string $name, string $presenter = null, array $bindings = [], bool $specialSidebar = false): ?string
    {
        if ($this->has($name)) {
            $instance = $this->instance($name);
            $params = array_values(Route::current()->parameters());

            $this->callbacks->get($name)->each(function ($callback) use ($instance, $params) {
                $callback($instance, ...$params);
            });

            return $instance->setBindings($bindings)->render($presenter, $specialSidebar);
        }

        return null;
    }

    /**
     * Get all menus.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->menus->toArray();
    }

    /**
     * Get count from all menus.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->menus->count();
    }

    /**
     * Empty the current menus.
     */
    public function destroy()
    {
        $this->menus = collect();

        return $this;
    }
}