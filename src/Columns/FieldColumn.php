<?php

namespace Plokko\LaravelTableHelper\Columns;

use Plokko\LaravelTableHelper\TableData;
use Plokko\LaravelTableHelper\TableHeader;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class FieldColumn extends TableColumn
{
    function __construct(
        string                         $name,
        ?string                        $label = null,
        bool                           $visible = true,
        public ?string                 $type = null,
        public null|bool|AllowedSort   $sort = null,
        public null|bool|AllowedFilter $filter = null,
    )
    {
        parent::__construct(
            name: $name,
            label: $label,
            visible: $visible,
        );
    }

    public function parse(TableData &$data): void
    {

        if ($this->filter) {
            $data->addFilter($this->getFilter());
        }
        if ($this->sort) {
            $data->addSort($this->getSort());
        }

        if ($this->visible) {
            $data->addHeader((
            new TableHeader(
                name: $this->name,
                title: $this->label,
                sortable: !!$this->sort,
                filterable: !!$this->filter,
                type: $this->type,
            ))
                ->translate($data->getFieldTranslationPrefix())
            );
        }
    }

    protected function getFilter(): AllowedFilter
    {
        if ($this->filter instanceof AllowedFilter) {
            return $this->filter;
        }

        ///TBD default filtering type based on field type
        return match ($this->type) {
            'date' => AllowedFilter::exact($this->name),
            'boolean' => AllowedFilter::exact($this->name),
            'number' => AllowedFilter::exact($this->name),

            'string' => AllowedFilter::partial($this->name),
            default => AllowedFilter::partial($this->name)
        };
    }

    protected function getSort(): AllowedSort
    {
        return $this->sort instanceof AllowedSort ? $this->sort : AllowedSort::field($this->name);
    }
}
