<?php

namespace Plokko\LaravelTableHelper;

use JsonSerializable;

abstract class NamedParamResource implements JsonSerializable
{
    /**
     * Define the parameter names in order
     * @var string[]
     */
    protected static array $params = [];
    /**
     * Define extra parameter not in props
     * @var string[]
     */
    protected static array $extraProps = [];

    ////
    protected array $props = [];

    /**
     * NamedParamResource constructor.
     * @param array $props
     */
    function __construct(
        ...$props
    )
    {
        foreach ($props as $key => $val) {
            $this->props[static::$params[$key] ?? $key] = $val;
        }
    }

    function __call($name, $arguments): self
    {
        $this->$name = $arguments[0];
        return $this;
    }

    function __get($name)
    {
        return $this->props[$name];
    }

    function __set($name, $value)
    {
        $this->props[$name] = $value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    function toArray(): array
    {
        $extraProps = [];
        foreach (static::$extraProps as $prop) {
            $extraProps[$prop] = $this->$prop;
        }
        return array_filter([...$this->props, ...$extraProps]);
    }
}
