<?php

namespace Twist\Library\Data;

use Twist\Library\Util\Arr;

/**
 * Class Repository
 *
 * @package Twist\Library\Data
 */
class Repository implements RepositoryInterface
{

    /**
     * @var array
     */
    private $items = [];

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @inheritdoc
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function has(string $key): bool
    {
        return Arr::has($this->items, $key);
    }

    /**
     * @inheritdoc
     */
    public function add(string $key, $value)
    {
        $this->items = Arr::add($this->items, $key, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, $value)
    {
        Arr::set($this->items, $key, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function fill($values)
    {
        Arr::map(static::getValues($values), [$this, 'set']);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function forget($keys)
    {
        Arr::forget($this->items, $keys);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($key, $value)
    {
        $this->set($key ?: $this->count(), $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($key)
    {
        $this->forget($key);
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @param array|mixed $values
     *
     * @return array
     */
    protected static function getValues($values): array
    {
        return Arr::dot(Arr::items($values));
    }

}