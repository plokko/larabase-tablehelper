<?php

namespace Plokko\LaravelTableHelper;


class TableHeader extends NamedParamResource
{
    protected static array $params = ['title', 'sortable', 'filterable', 'format', 'component'];
    protected static array $extraProps = ['name', 'type'];

    /**
     * Defines a new table header.
     * @param string $name
     * @param ?string $type
     * @param ?string $title
     * @param ?bool $sortable
     * @param ?bool $filterable
     * @param mixed $format
     * @param mixed $component
     */
    function __construct(
        public readonly string  $name,
        public readonly ?string $type = null,
                                ...$props,
    )
    {
        parent::__construct(...$props);
    }
}
