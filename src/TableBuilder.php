<?php

namespace Plokko\LaravelTableHelper;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class TableBuilder
{
    public ?string $fieldTranslationPrefix = null;
    protected string $joinChar = '.';
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
    ): TableBuilder
    {
        return new self(
            subject: $subject,
            name: $name
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

    public function fieldTranslationPrefix(?string $fieldTranslationPrefix): self
    {
        $this->fieldTranslationPrefix = $fieldTranslationPrefix;
        return $this;
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
}
