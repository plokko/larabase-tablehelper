<?php

namespace Plokko\LaravelTableHelper;

use Illuminate\Http\Request;
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
        $data = $this->builder
            ->allowedFilters($this->filter)
            ->allowedSorts($this->sort)
            ->paginate() //TBD
            ->toArray();

        $data['prefix'] = $this->table->getPrefix();
        $data['headers'] = $this->headers;
        return $data;
    }

    public function getCurrentPage(): int
    {
        return intval($this->request->input("page", 1));
    }


    public function addHeader(TableHeader $header): void
    {
        $this->headers[] = $header;
    }

    public function addFilter($filter): void
    {
        $this->filter[] = $filter;
    }

    public function addSort($sort): void
    {
        $this->sort[] = $sort;
    }

    public function getFieldTranslationPrefix(): ?string
    {
        return $this->table->fieldTranslationPrefix;
    }
}
