<?php

namespace Plokko\LaravelTableHelper\Columns;

use Plokko\LaravelTableHelper\Columns\ActionColumn\Action;
use Plokko\LaravelTableHelper\TableData;
use Plokko\LaravelTableHelper\TableHeader;

class ActionColumn extends DisplayColumn
{


    function __construct(
        string                   $name,
        ?string                  $label = null,
        bool                     $visible = true,
        protected array          $actions = [],
        public null|string|array $actionsLocalization = null,
    )
    {
        parent::__construct(
            name: $name,
            label: $label,
            visible: $visible,
        );
    }

    public function actions(array $actions): self
    {
        $this->actions = $actions;
        return $this;
    }


    public function parse(TableData &$data): void
    {
        if ($this->visible) {
            $data->addHeader((
            new TableHeader(
                type: 'action',
                name: $this->name,
                title: $this->label,
                //sortable: false,
                //filterable: false,
                actions: $this->actions, /// Action list
                actionsLocalization: $this->actionsLocalization, // Action localization
            ))
                ->translate($data->getcolumnsLocalization()));
        }
    }


    public function actionsLocalization(null|string|array $actionsLocalization): self
    {
        $this->actionsLocalization = $actionsLocalization;
        return $this;
    }
}
