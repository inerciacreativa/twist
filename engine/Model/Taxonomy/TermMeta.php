<?php

namespace Twist\Model\Taxonomy;

use Twist\Model\Meta\Meta;

/**
 * Class TermMeta
 *
 * @package Twist\Model\Taxonomy
 */
class TermMeta extends Meta
{

    /**
     * TermMeta constructor.
     *
     * @param Term $term
     */
    public function __construct(Term $term)
    {
        parent::__construct($term, 'term');
    }

}