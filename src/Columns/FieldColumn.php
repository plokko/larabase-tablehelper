<?php

namespace Plokko\LaravelTableHelper\Columns;

use Plokko\LaravelTableHelper\TableColumn;
use Plokko\LaravelTableHelper\TableData;
use Plokko\LaravelTableHelper\TableHeader;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class FieldColumn extends TableColumn
{
    function __construct(
        string                                $name,
        ?string                               $label = null,
        bool                                  $visible = true,
        public ?string                        $type = null,
        public null|bool|string|AllowedSort   $sort = null,
        public null|bool|string|AllowedFilter $filter = null,
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
        if ($sort = $this->parseSort($this->sort ?? $data->getColumnDefault('sort'))) {
            $data->addSort($sort);
        }
        if ($filter = $this->parseFilter($this->filter ?? $data->getColumnDefault('filter'))) {
            $data->addFilter($filter);
        }

        if ($this->visible) {
            $data->addHeader((
            new TableHeader(
                name: $this->name,
                title: $this->label,
                sortable: !!$sort,
                filterable: !!$filter,
                format: $this->type,
            ))
                ->translate($data->getcolumnsLocalization())
            );
        }
    }

    protected function parseSort(null|bool|string|AllowedSort $value): AllowedSort
    {
        if ($value === false || $value === null) {
            return null;
        }
        if ($value instanceof AllowedSort) {
            return $value;
        }

        $field = (is_string($value)) ? $value : $this->name;
        return AllowedSort::field($field);
    }

    protected function parseFilter(null|bool|string|AllowedFilter $value): ?AllowedFilter
    {
        if ($value === false || $value === null) {
            return null;
        }
        if ($value instanceof AllowedFilter) {
            return $value;
        }


        $field = (is_string($value)) ? $value : $this->name;
        ///TBD default filtering type based on field type
        return match ($this->type) {
            'date' => AllowedFilter::exact($field),
            'boolean' => AllowedFilter::exact($field),
            'number' => AllowedFilter::exact($field),

            'string' => AllowedFilter::partial($field),
            default => AllowedFilter::partial($field)
        };
    }

    /**
     * Set sort
     * @param null|bool|AllowedSort $sort
     */
    public function sort(null|bool|AllowedSort $sort = null): self
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * Set filters
     * @param null|bool|AllowedFilter $filter
     */
    public function filter(null|bool|AllowedFilter $filter = null): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Set type
     */
    public function type(?string $type = null): self
    {
        $this->type = $type;
        return $this;
    }
}
