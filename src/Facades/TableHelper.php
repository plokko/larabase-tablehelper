<?php

namespace Plokko\LaravelTableHelper\Facades;

use Illuminate\Support\Facades\Facade;
use Plokko\LaravelTableHelper\TableHelperAccessor;

/**
 * @method static \Plokko\LaravelTableHelper\TableBuilder make(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|string $subject)
 */
class TableHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TableHelperAccessor::class;
    }
}
