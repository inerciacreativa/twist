<?php

namespace Twist\Template\Taxonomy;

use Twist\Template\Post\Post;

class TagTaxonomy extends Taxonomy
{

    public function __construct(Post $post = null)
    {
        parent::__construct('post_tag', $post);
    }

    protected function isCurrentTaxonomy()
    {
        if (is_null($this->currentTaxonomy)) {
            $this->currentTaxonomy = is_tag();
        }

        return $this->currentTaxonomy;
    }

    protected function isCurrentTerm($term)
    {
        if ($this->isCurrentTaxonomy() && is_tag($term->slug)) {
            return true;
        }

        return false;
    }

}