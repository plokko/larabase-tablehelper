<?php

namespace Plokko\LaravelTableHelper\Enums;

enum SelectionStrategy: string
{
    case single = 'single';
    case page = 'page';
    case all = 'all';
}
