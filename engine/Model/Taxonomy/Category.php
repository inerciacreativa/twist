<?php

namespace Twist\Model\Taxonomy;

use Twist\Model\Post\Post;

/**
 * Class Category
 *
 * @package Twist\Model\Taxonomy
 */
class Category extends Taxonomy
{

    /**
     * Category constructor.
     *
     * @param Post|null $post
     */
    public function __construct(Post $post = null)
    {
        parent::__construct('category', $post);
    }

    /**
     * @inheritdoc
     */
    protected function isCurrentTaxonomy()
    {
        if ($this->currentTaxonomy === null) {
            $this->currentTaxonomy = is_category();
        }

        return $this->currentTaxonomy;
    }

    /**
     * @inheritdoc
     */
    protected function isCurrentTerm($term)
    {
        return $this->isCurrentTaxonomy() && is_category($term->term_id);
    }

}