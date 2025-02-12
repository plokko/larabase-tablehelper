<?php

namespace Plokko\LaravelTableHelper;

use Plokko\LaravelTableHelper\Columns\ActionColumn;
use Plokko\LaravelTableHelper\Columns\DisplayColumn;
use Plokko\LaravelTableHelper\Columns\FieldColumn;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

abstract class TableColumn
{
    function __construct(
        public readonly string $name,
        public ?string         $label,
        public bool            $visible = true,
    )
    {
    }

    /**
     * Create a new column for a table field.
     */
    static function field(
        string                  $name,
        ?string                 $label = null,
        bool                    $visible = true,
        ///
        ?string                 $type = null,
        null|bool|AllowedSort   $sort = null,
        null|bool|AllowedFilter $filter = null,
    ): FieldColumn
    {
        return new FieldColumn(
            name: $name,
            label: $label,
            visible: $visible,
            type: $type,
            sort: $sort,
            filter: $filter,
        );
    }

    /**
     * Create a new column for display.
     */
    static function display(
        string  $name,
        ?string $label = null,
        bool    $visible = true,
    ): DisplayColumn
    {
        return new DisplayColumn(
            name: $name,
            label: $label,
            visible: $visible,
        );
    }

    /**
     * Create a new column for actions.
     */
    static function actions(
        string  $name,
        ?string $label = null,
        bool    $visible = true,
    ): ActionColumn
    {
        return new ActionColumn(
            name: $name,
            label: $label,
            visible: $visible,
        );
    }

    /**
     * Parse TableData adding informations to the query.
     */
    abstract public function parse(TableData &$data): void;
}
