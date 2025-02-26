<?php

namespace Plokko\LaravelTableHelper\Columns\ActionColumn;

use Plokko\LaravelTableHelper\NamedParamResource;

class RowAction extends NamedParamResource
{

    protected static array $params = ['route', 'method', 'link', 'label', 'icon', 'color', 'confirm'];
    protected static array $extraProps = ['name'];

    /**
     * @param string $name
     * @param ?string $route
     * @param ?string $method
     * @param ?string $link
     * @param ?string $label
     * @param ?string $icon
     * @param ?string $color
     * @param ?string $confirm
     */
    function __construct(
        public readonly string $name,
                               ...$props,
    )
    {
        parent::__construct(...$props);
    }


    /**
     * Makes a new action
     *
     * @param string $name
     * @param ?string $route
     * @param ?string $link
     * @param ?string $label
     * @param ?string $icon
     * @param ?string $color
     */
    public static function make(string $name, ...$values): RowAction
    {
        return new RowAction($name, ...$values);
    }

    /**
     * Generate actions for a resource
     *
     * @param string $resource name of the resource
     * @param array $actions list of actions to generate (default: ['view','edit','delete'])
     *
     * @return RowAction[] List of generated actions
     */
    public static function forResource(
        string $resource,
        array  $actions = [
            'view',
            'edit',
            'destroy'
        ]
    ): array
    {
        return array_map((fn($action) => new static(
            name: $action,
            route: "$resource.$action",
            confirm: ($action === 'destroy') ? trans('common.confirm-delete') : null,
            method: ($action === 'destroy') ? 'delete' : null,
        )), $actions);
    }
}
