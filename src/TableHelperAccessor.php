<?php

namespace Plokko\LaravelTableHelper;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

class TableHelperAccessor
{
    public function __construct(protected array $config) {}

    public function make(EloquentBuilder|Relation|string $subject): TableBuilder
    {
        return new TableBuilder(
            subject: $subject
        );
    }
}
