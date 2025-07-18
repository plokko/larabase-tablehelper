<?php

namespace Plokko\LaravelTableHelper;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class TableData
{
    public function __construct(
        public readonly TableBuilder $table,
        public readonly Request $request,
        public QueryBuilder $builder,
        public array $headers = [],
        public array $filter = [],
        public array $sort = [],
        protected null|AllowedSort|array|string $defaultSorts = null,
    ) {

        if ($this->defaultSorts !== null) {
            $this->builder->defaultSorts($this->defaultSorts);
        }
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getData(): array
    {
        $pageSize = $this->table->pageSize;
        $allowedPageSizes = $this->table->allowedPageSizes;

        //change page size on user input
        $perPage = $this->request->input('perPage');
        if (in_array($perPage, $allowedPageSizes)) {
            $pageSize = $perPage;
        }

        $result = $this->builder
            ->allowedFilters($this->filter)
            ->allowedSorts($this->sort)
            ->paginate($pageSize);

        $resource = call_user_func([($this->table->resource ?: JsonResource::class), 'collection'], $result->items());

        $orderBy = $this->getSortParams();

        /** @var AnonymousResourceCollection $resource */
        $data = [
            'data' => $resource->toArray($this->request),

            /// Extra
            'order_by' => $orderBy,
            ///-- Options
            'headers' => $this->headers,
            'search' => $this->table->searchOptions,

            /// Paginator
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'per_page' => $result->perPage(),
            'total' => $result->total(),
            //
            'allowedPageSizes' => $allowedPageSizes,
        ];

        return $data;
    }

    public function getSortParams(): array
    {
        $param = config('query-builder.parameters.sort');

        $sort = $this->request->has($param) ? array_filter(explode(',', $this->request->input($param))) : null;

        return empty($sort) ? $this->defaultSorts ?? [] : $sort;
    }

    public function getCurrentPage(): int
    {
        return intval($this->request->input('page', 1));
    }

    public function addHeader(TableHeader $header): self
    {
        $this->headers[] = $header;

        return $this;
    }

    public function addFilter(string|AllowedFilter $filter): self
    {
        $this->filter[] = $filter;

        return $this;
    }

    public function addSort(string|AllowedSort $sort): self
    {
        $this->sort[] = $sort;

        return $this;
    }

    public function getcolumnsLocalization(): ?string
    {
        return $this->table->columnsLocalization;
    }

    public function getColumnDefault(string $param): mixed
    {
        return $this->table->getColumnDefault($param);
    }
}
