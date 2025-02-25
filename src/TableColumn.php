<?php

namespace Plokko\LaravelTableHelper;

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
     * Parse TableData adding informations to the query.
     */
    abstract public function parse(TableData &$data): void;
}
