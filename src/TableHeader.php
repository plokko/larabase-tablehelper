<?php

namespace Plokko\LaravelTableHelper;

use JsonSerializable;

class TableHeader implements JsonSerializable
{
    function __construct(
        public readonly string $name,
        public ?string         $title = null,
        public ?bool           $sortable = null,
        public ?bool           $filterable = null,
        public mixed           $type = null,
        public mixed           $component = null,
        public array           $props = [],
    )
    {
    }

    public function translate(?string $transPrefix): self
    {
        if ($transPrefix !== null && $this->title === null) {
            $this->title = trans($transPrefix . '.' . $this->name);
        }
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'value' => $this->name,
            ...array_filter([
                'title' => $this->title,
                'sortable' => $this->sortable,
                'filterable' => $this->filterable,
                'type' => $this->type,
                'component' => $this->component,
            ]),
            ...$this->props,
        ];
    }
}
