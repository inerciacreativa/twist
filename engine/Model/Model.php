<?php

namespace Twist\Model;

/**
 * Class Model
 *
 * @package Twist\Model
 */
abstract class Model
{

    /**
     * @var Model
     */
    protected $parent;

    /**
     * @var ModelCollection
     */
    protected $children;

    /**
     * @return int
     */
    public function id(): int
    {
        return 0;
    }

    /**
     * Whether the model has a parent.
     *
     * @return bool
     */
    public function has_parent(): bool
    {
        return $this->parent !== null;
    }

    /**
     * Retrieve the model's parent.
     *
     * @return Model|null
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Wheter the model has children.
     *
     * @return bool
     */
    public function has_children(): bool
    {
        return (bool)$this->getChildren()->count();
    }

    /**
     * Retrieve the children.
     *
     * @return ModelCollection
     */
    public function children()
    {
        return $this->getChildren();
    }

    /**
     * Set the model's parent.
     *
     * @param Model $model
     *
     * @return $this
     */
    protected function setParent(Model $model)
    {
        $this->parent = $model;

        return $this;
    }

    /**
     * @return ModelCollection
     */
    protected function setChildren()
    {
        return new ModelCollection($this);
    }

    /**
     * @return ModelCollection
     */
    protected function getChildren()
    {
        if ($this->children === null) {
            $this->children = $this->setChildren();
        }

        return $this->children;
    }

}