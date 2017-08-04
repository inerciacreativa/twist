<?php

namespace Twist\Model;

/**
 * Class Collection
 *
 * @package Twist\Model
 */
class ModelCollection implements \Countable, \Iterator
{

    /**
     * @var Model
     */
    protected $parent;

    /**
     * @var array
     */
    protected $children = [];

    /**
     * Collection constructor.
     *
     * @param Model|null $parent
     */
    public function __construct(Model $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return bool
     */
    public function has_parent()
    {
        return $this->parent !== null;
    }

    /**
     * @return Model|null
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * @param int $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->children);
    }

    /**
     * @param int $key
     *
     * @return Model|null
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->children[$key];
        }

        return null;
    }

    /**
     * @param Model $child
     *
     * @return $this
     */
    public function add(Model $child)
    {
        $this->children[$child->id()] = $child;

        return $this;
    }

    /**
     * Returns the number of children.
     *
     * @return int
     */
    public function count()
    {
        return count($this->children);
    }

    /**
     * Rewind to the first item.
     */
    public function rewind()
    {
        reset($this->children);
    }

    /**
     * Move forward to next item.
     */
    public function next()
    {
        next($this->children);
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     */
    public function valid()
    {
        return key($this->children) !== null;
    }

    /**
     * Returns the current item.
     *
     * @return Model
     */
    public function current()
    {
        return current($this->children);
    }

    /**
     * Returns the id of the current item.
     *
     * @return int
     */
    public function key()
    {
        return key($this->children);
    }

}