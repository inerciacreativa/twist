<?php

namespace Twist\Model\Taxonomy;

use Twist\Model\ModelArray;

/**
 * Class Metas
 *
 * @package Twist\Model\Taxonomy
 */
class Metas extends ModelArray
{

    /**
     * Metas constructor.
     *
     * @param Term $term
     */
    public function __construct(Term $term)
    {
        parent::__construct(get_metadata('term', $term->id()), $term);
    }

    /**
     * @param string $name
     *
     * @return array|mixed
     */
    public function offsetGet($name)
    {
        $value = parent::offsetGet($name);

        if (!\is_array($value)) {
            return null;
        }

        return \count($value) === 1 ? maybe_unserialize($value[0]) : array_map('maybe_unserialize', $value);
    }

}