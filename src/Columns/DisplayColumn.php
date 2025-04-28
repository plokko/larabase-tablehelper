<?php

namespace Plokko\LaravelTableHelper\Columns;

use Plokko\LaravelTableHelper\TableColumn;
use Plokko\LaravelTableHelper\TableData;
use Plokko\LaravelTableHelper\TableHeader;

class DisplayColumn extends TableColumn
{
    public function __construct(
        string $name,
        ?string $label = null,
        bool $visible = true,
    ) {
        parent::__construct(
            name: $name,
            label: $label,
            visible: $visible,
        );
    }

    public function parse(TableData &$data): void
    {
        if ($this->visible) {
            $data->addHeader((
            new TableHeader(
                type: 'display',
                name: $this->name,
                title: $this->label,
                sortable: false,
                filterable: false,
            ))
                ->translate($data->getcolumnsLocalization()));
        }
    }
}
