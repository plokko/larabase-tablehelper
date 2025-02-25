<?php

namespace Plokko\LaravelTableHelper\Columns\ActionColumn;

use Plokko\LaravelTableHelper\NamedParamResource;

class RowAction extends NamedParamResource
{

    protected static array $params = ['route', 'link', 'label', 'icon', 'color'];
    protected static array $extraProps = ['name'];

    /**
     * @param string $name
     * @param ?string $route
     * @param ?string $link
     * @param ?string $label
     * @param ?string $icon
     * @param ?string $color
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
            'delete'
        ]
    ): array
    {
        return array_map((fn($action) => new static(
            name: $action,
            route: "$resource.$action",
        )), $actions);
    }
}
