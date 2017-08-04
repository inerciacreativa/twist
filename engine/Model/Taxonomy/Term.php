<?php

namespace Twist\Model\Taxonomy;

use Twist\Model\Model;
use Twist\Model\ModelCollection;

/**
 * Class Term
 *
 * @package Twist\Model\Taxonomy
 */
class Term extends Model
{

    /**
     * @var \stdClass
     */
    protected $term;

    /**
     * Term constructor.
     *
     * @param ModelCollection $terms
     * @param \stdClass|\WP_Term $term
     */
    public function __construct(ModelCollection $terms, $term)
    {
        $this->term = $term;
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return (int)$this->term->term_id;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->term->name;
    }

    /**
     * @return string
     */
    public function link(): string
    {
        return get_term_link($this->term);
    }

    /**
     * @return string
     */
    public function feed(): string
    {
        return get_term_feed_link($this->id(), $this->taxonomy());
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->term->description;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return (int)$this->term->count;
    }

    /**
     * @return string
     */
    public function taxonomy(): string
    {
        return $this->term->taxonomy;
    }

    /**
     * @return bool
     */
    public function current(): bool
    {
        return isset($this->term->current);
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function classes(string $prefix = ''): string
    {
        if ($prefix) {
            $classes = array(
                $prefix . '-item',
                $prefix . '-' . $this->taxonomy(),
            );
        } else {
            $prefix  = $this->taxonomy();
            $classes = array(
                $prefix,
                $prefix . '-' . $this->term->slug,
            );
        }

        if ($this->current()) {
            $classes[] = $prefix . '-current';
        }

        return implode(' ', $classes);
    }

}