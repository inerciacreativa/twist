<?php

namespace Twist\Template\Taxonomy;

class CustomTaxonomy extends Taxonomy
{

    protected function isCurrentTaxonomy()
    {
        if (is_null($this->currentTaxonomy)) {
            $this->currentTaxonomy = is_tax($this->name());
        }

        return $this->currentTaxonomy;
    }

    protected function isCurrentTerm($term)
    {
        if ($this->isCurrentTaxonomy() && is_tax($this->name(), $term->term_id)) {
            return true;
        }

        return false;
    }

}