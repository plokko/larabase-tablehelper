<?php

namespace Plokko\LaravelTableHelper\Columns\ActionColumn;

use Plokko\LaravelTableHelper\NamedParamResource;

class RowAction extends NamedParamResource
{
    protected static array $params = ['route', 'routeBindings', 'method', 'link', 'label', 'icon', 'color', 'confirm'];

    protected static array $extraProps = ['name'];

    /**
     * @param  ?string  $route
     * @param  null|string|array  $routeBindings
     * @param  ?string  $method
     * @param  ?string  $link
     * @param  ?string  $label
     * @param  ?string  $icon
     * @param  ?string  $color
     * @param  ?string  $confirm
     */
    public function __construct(
        public readonly string $name,
        ...$props,
    ) {
        parent::__construct(...$props);
    }

    /**
     * Makes a new action
     *
     * @param  ?string  $route
     * @param  null|string|array  $routeBindings
     * @param  ?string  $link
     * @param  ?string  $label
     * @param  ?string  $icon
     * @param  ?string  $color
     */
    public static function make(string $name, ...$values): RowAction
    {
        return new RowAction($name, ...$values);
    }

    /**
     * Generate actions for a resource
     *
     * @param  string  $resource  name of the resource
     * @param  array  $actions  list of actions to generate (default: ['view','edit','delete'])
     * @return RowAction[] List of generated actions
     */
    public static function forResource(
        string $resource,
        array $actions = [
            'show',
            'edit',
            'destroy',
        ],
        null|string|array $routeBindings = null,
    ): array {
        $items = [];
        foreach ($actions as $key => $action) {
            $name = is_array($action) ? $key : $action;

            $defaults = [
                'route' => "$resource.$name",
                'confirm' => ($name === 'destroy') ? trans('common.confirm-delete') : null,
                'method' => ($name === 'destroy') ? 'delete' : null,
                'routeBindings' => $routeBindings,
            ];

            $items[] = new static(
                ...(is_array($action) ?
                    [
                        ...$defaults,
                        ...$action,
                        'name' => $key,
                    ] :

                    [
                        ...$defaults,
                        'name' => $name,
                    ])
            );
        }

        return $items;
    }
}
