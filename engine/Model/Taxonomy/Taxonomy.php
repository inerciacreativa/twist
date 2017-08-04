<?php

namespace Twist\Model\Taxonomy;

use Twist\Model\ModelCollection;
use Twist\Model\Post\Post;

/**
 * Class Taxonomy
 *
 * @package Twist\Model\Taxonomy
 */
abstract class Taxonomy
{

    /**
     * @var \WP_Taxonomy
     */
    protected $taxonomy;

    protected $terms;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var Post
     */
    protected $post;

    /**
     * @var bool
     */
    protected $currentTaxonomy;

    /**
     * @var Term
     */
    protected $currentTerm;

    /**
     * Taxonomy constructor.
     *
     * @param string    $taxonomy
     * @param Post|null $post
     *
     * @throws \RuntimeException
     */
    public function __construct($taxonomy, Post $post = null)
    {
        $this->taxonomy = get_taxonomy($taxonomy);

        if (!$this->taxonomy) {
            throw new \RuntimeException("The taxonomy '$taxonomy' does not exists");
        }

        $this->post = $post;

        if ($post === null) {
            $this->terms = [];
        }
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->taxonomy->name;
    }

    /**
     * @return bool
     */
    public function is_hierarchical()
    {
        return $this->taxonomy->hierarchical;
    }

    public function terms(array $options = array())
    {
        return $this->post === null ? $this->getTerms($options) : $this->getPostTerms();
    }

    public function current()
    {
        if ($this->currentTerm === null) {
            $this->currentTerm = false;

            if ($this->isCurrentTaxonomy()) {
                $this->currentTerm = $this->terms()->find(get_queried_object_id());
            }
        }

        return $this->currentTerm;
    }

    /**
     * @return bool
     */
    abstract protected function isCurrentTaxonomy();

    /**
     * @return bool
     */
    abstract protected function isCurrentTerm($term);

    /**
     * @param array $arguments
     *
     * @return array
     */
    public function getTermsArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $arguments;
    }

    protected function getTerms(array $options = array())
    {
        $key = md5(serialize($options));

        if (!isset($this->terms[$key])) {
            add_filter('get_terms_args', [$this, 'getTermsArguments']);
            $terms = get_terms($this->name(), $options);
            remove_filter('get_terms_args', [$this, 'getTermsArguments']);

            $parent = 0;
            if ($this->arguments['parent'] || $this->arguments['child_of']) {
                $parent = $this->arguments['parent'] ?: $this->arguments['child_of'];
            }

            $this->terms[$key] = $this->getNestedCollection($terms, $parent);
        }

        return $this->terms[$key];
    }

    /**
     * @param array $terms
     * @param int   $parent
     *
     * @return ModelCollection
     */
    protected function getNestedCollection(array &$terms, $parent = 0)
    {
        if ($parent instanceof Term) {
            $collection = new ModelCollection($parent);
            $parent_id  = $parent->id();
        } else {
            $collection = new ModelCollection();
            $parent_id  = $parent;
        }

        foreach ($terms as $term) {
            if ($term->parent === $parent_id) {
                if ($this->isCurrentTerm($term)) {
                    $term->current = true;
                }

                $term = new Term($term, $collection);
                $collection->add($term);

                $children = $this->getNestedCollection($terms, $term);
                if ($children->count()) {
                    $term->add($children);
                }
            }
        }

        return $collection;
    }

    protected function getPostTerms()
    {
        if ($this->terms === null) {
            $terms = get_the_terms($this->post->id(), $this->name());

            $this->terms = $this->getCollection($terms);
        }

        return $this->terms;
    }

    protected function getCollection(array &$terms)
    {
        $collection = new ModelCollection();

        foreach ($terms as $term) {
            if ($this->isCurrentTerm($term)) {
                $term->current = true;
            }

            $collection->add(new Term($term));
        }

        return $collection;
    }

}