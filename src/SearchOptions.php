<?php

namespace Plokko\LaravelTableHelper;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class SearchOptions implements Arrayable, JsonSerializable
{
    public function __construct(
        public string|array $field,
        public null|array|bool $options = null,
        public bool $show = true,
        public string $name = 'string',
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'field' => $this->field,
            'options' => $this->options,
            'show' => $this->show,
            'name' => $this->name,
        ]);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
