<?php

namespace Plokko\LaravelTableHelper\Facades;

use Illuminate\Support\Facades\Facade;
use Plokko\LaravelTableHelper\TableHelperAccessor;

class TableHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TableHelperAccessor::class;
    }
}
