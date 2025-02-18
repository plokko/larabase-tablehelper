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

    function __construct(
        public readonly TableBuilder $table,
        public readonly Request      $request,
        public QueryBuilder          $builder,
        public array                 $headers = [],
        public array                 $filter = [],
        public array                 $sort = [],
    )
    {
    }


    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getData(): array
    {
        //QueryBuilder::for($this->subject, $rq)
        $result = $this->builder
            ->allowedFilters($this->filter)
            ->allowedSorts($this->sort)
            ->paginate() //TBD
        ;

        $resource = call_user_func([($this->table->resource ?: JsonResource::class), 'collection'], $result->items());
        /** @var AnonymousResourceCollection $resource */
        $data = [
            'data' => $resource->toArray($this->request),
            /// Extra
            'order_by' => $this->table->getPrefix(),
            'headers' => $this->headers,
            /// Paginator
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'per_page' => $result->perPage(),
            'total' => $result->total(),
        ];

        return $data;
    }

    public function getCurrentPage(): int
    {
        return intval($this->request->input("page", 1));
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

    public function getfieldLocalization(): ?string
    {
        return $this->table->fieldLocalization;
    }


    public function getColumnDefault(string $param): mixed
    {
        return $this->table->getColumnDefault($param);
    }
}
