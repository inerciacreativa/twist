<?php

namespace Twist\Model;

/**
 * Class ModelArray
 *
 * @package Twist\Model
 */
abstract class ModelArray extends Model implements \ArrayAccess, \IteratorAggregate, \Countable
{

    /**
     * @var array
     */
    protected $properties;

    /**
     * Accessor constructor.
     *
     * @param array $properties
     * @param Model $parent
     */
    public function __construct(array $properties, Model $parent = null)
    {
        $this->properties = $properties;

        if ($parent !== null) {
            $this->setParent($parent);
        }
    }

    /**
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->properties);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->properties);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function offsetExists($name): bool
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function offsetSet($name, $value)
    {
    }

    /**
     * @param string $name
     */
    public function offsetUnset($name)
    {
    }

    /**
     * Get an iterator for the values.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->properties);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setValue($name, $value)
    {
        $this->properties[$name] = $value;
    }

}