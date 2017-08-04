<?php

namespace Twist\Model\Taxonomy;

/**
 * Class Walker
 *
 * @package Twist\Model\Taxonomy
 */
class Walker extends \Walker_Category
{

    /**
     * @var Terms
     */
    protected $terms;

    /**
     * @var Term
     */
    protected $term;

    /**
     * Walker constructor.
     *
     * @param Terms $terms
     */
    public function __construct(Terms $terms = null)
    {
        $this->terms = $terms;
    }

    /**
     * @inheritdoc
     */
    public function start_el(&$output, $term, $depth = 0, $arguments = [], $id = 0)
    {
        $this->term = new Term($this->terms, $term);
        
        $this->terms->add($this->term);
    }

    /**
     * @inheritdoc
     */
    public function end_el(&$output, $term, $depth = 0, $args = [])
    {
    }

    /**
     * @inheritdoc
     */
    public function start_lvl(&$output, $depth = 0, $args = [])
    {
        $this->terms = $this->term->children();
    }

    /**
     * @inheritdoc
     */
    public function end_lvl(&$output, $depth = 0, $args = [])
    {
        $this->terms = $this->term->parent()->children();
    }

}
