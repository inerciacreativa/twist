<?php

namespace Twist\Model\Navigation;

use Twist\Model\ModelArray;

/**
 * Class Link
 *
 * @package Twist\Model\Navigation
 */
class Link extends ModelArray
{

    /**
     * Link constructor.
     *
     * @param Links $items
     * @param array $properties
     */
    public function __construct(Links $items, array $properties)
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
     * @return Links
     */
    protected function setChildren(): Links
    {
        return new Links($this);
    }

	/**
	 * @return string
	 */
    public function __toString(): string
    {
	    return (string)$this['title'];
    }
}