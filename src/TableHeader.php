<?php

namespace Plokko\LaravelTableHelper;

class TableHeader extends NamedParamResource
{
    protected static array $params = ['title', 'sortable', 'filterable', 'format', 'component'];

    protected static array $extraProps = ['name', 'type'];

    /**
     * Defines a new table header.
     *
     * @param  ?string  $title
     * @param  ?bool  $sortable
     * @param  ?bool  $filterable
     * @param  mixed  $format
     * @param  mixed  $component
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $type = null,
        public ?string $translate = null,
        ...$props,
    ) {
        parent::__construct(...$props);
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        if ($this->translate && ($data['title'] ?? null) == null) {
            $data['title'] = trans($this->translate . '.' . $this->name);
        }

        return $data;
    }

    public function translate(?string $translate): self
    {
        $this->translate = $translate;

        return $this;
    }
}
