<?php

namespace Twist\Model\Post;

use Twist\Model\ModelCollection;
use Twist\Model\Taxonomy\Term;

/**
 * Class Taxonomies
 *
 * @package Twist\Model\Post
 */
class Terms extends ModelCollection
{

    /**
     * Taxonomies constructor.
     *
     * @param Post   $post
     * @param string $taxonomy
     */
    public function __construct(Post $post, $taxonomy)
    {
        parent::__construct();

        $terms = get_the_terms($post->object(), $taxonomy);

        if (is_array($terms)) {
          $this->children = $terms;
        }
    }

    /**
     * @return Term
     */
    public function current()
    {
        $term = parent::current();

        return new Term($this, $term);
    }

}