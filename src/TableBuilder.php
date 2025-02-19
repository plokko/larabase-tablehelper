<?php

namespace Plokko\LaravelTableHelper;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class TableBuilder
{
    public ?string $fieldLocalization = null;
    public ?string $resource = null;
    protected string $joinChar = '.';

    protected array $columnDefaults = [
        'label' => null,
        'type' => null,
        'sort' => null,
        'filter' => null,
    ];


    /**
     * @var array<TableColumn> Table column declaration
     */
    protected array $columns = [];

    function __construct(
        protected EloquentBuilder|Relation|string $subject,
        public ?string                            $name = null,
    )
    {
    }

    static function make(
        ?string                         $name,
        EloquentBuilder|Relation|string $subject,
        ?string                         $resource = null
    ): TableBuilder
    {
        return new self(
            subject: $subject,
            name: $name,
        );
    }

    /**
     * Set the fields for the table
     * @param array<string|TableColumn> $fields
     */
    public function columns(array $columns): self
    {
        $this->columns = array_map(fn($c) => is_string($c) ? TableColumn::field(name: $c) : $c, $columns);
        return $this;
    }

    /**
     * Set column default values
     * @param ?string $label Default label, if not specified and no field translation are specified
     * @param ?string $type
     * @param callable(string $fieldName):null|bool|AllowedSort $sort
     * @param callable(string $fieldName):null|bool|AllowedFilter $filter
     */
    public function columnDefaults(
        ?string                          $label = null,
        ?string                          $type = null,
        null|bool|AllowedSort|callable   $sort = null,
        null|bool|AllowedFilter|callable $filter = null,
    ): self
    {
        $this->columnDefaults = compact('label', 'type', 'sort', 'filter');
        return $this;
    }

    public function fieldLocalization(?string $fieldLocalization): self
    {
        $this->fieldLocalization = $fieldLocalization;
        return $this;
    }

    public function useResource(?string $resource): self
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Register table
     */
    public function register(?string $name = null): void
    {
        $this->name = $name;

        if ($name === null) {
            $name = 'default';
        }

        $prefix = '_tables'; //TODO: TBD
        Inertia::share(
            "$prefix.$name",
            fn(Request $request) => $this->getData($request),
        );
    }

    /**
     * Returns the data to display to the front-end.
     *
     * @return array<string, mixed> Data to display to the front-end.
     */
    public function getData(?Request $request = null): array
    {
        return $this->getTableData($request)->getData();
    }

    /**
     * Process the table options.
     */
    public function getTableData(?Request $request = null): TableData
    {
        $rq = $this->parseRequest($request ?? request());

        $dt = new TableData(
            table: $this,
            request: $rq,
            builder: QueryBuilder::for($this->subject, $rq),
        );

        foreach ($this->columns as $column) {
            /**@var TableColumn $column */
            $column->parse($dt);
        }

        return $dt;
    }

    /**
     * Parse request removing table prefix.
     *
     * @return Request New request with table prefix removed.
     */
    protected function parseRequest(Request $request): Request
    {
        $prefix = $this->getPrefix();
        if ($prefix == null) {
            return $request;
        }

        $prefixLen = strlen($prefix);
        $query = $request->query();

        foreach ($query as $k => $v) {
            if (in_array($k, [
                ///TODO: use names from config?
                $prefix . 'sort',
                $prefix . 'filter',
                $prefix . 'page',
            ])) {
                $k = substr($k, $prefixLen);
                $query[$k] = $v;
            }
        }

        return $request->duplicate(
            query: $query,
        );
    }

    public function getPrefix(): ?string
    {
        return ($this->name == null) ? null : $this->name . $this->joinChar;
    }

    /**
     * @internal
     */
    public function getColumnDefault(string $param): mixed
    {
        return $this->columnDefaults[$param];
    }
}
