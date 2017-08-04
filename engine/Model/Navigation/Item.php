<?php

namespace Twist\Model\Navigation;

use Twist\Model\ModelArray;

/**
 * Class Item
 *
 * @package Twist\Model\Navigation
 */
class Item extends ModelArray
{

    /**
     * Item constructor.
     *
     * @param Items $items
     * @param array $properties
     */
    public function __construct(Items $items, array $properties)
    {
        parent::__construct($properties, $items->parent());
    }

    /**
     * @inheritdoc
     */
    public function id(): int
    {
        return (int)$this->offsetGet('id');
    }

    /**
     * @return Items
     */
    protected function setChildren(): Items
    {
        return new Items($this);
    }

}