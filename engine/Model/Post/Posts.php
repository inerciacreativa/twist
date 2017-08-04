<?php

namespace Twist\Model\Post;

use Twist\Model\ModelCollection;

/**
 * Class Posts
 *
 * @package Twist\Model\Post
 */
class Posts extends ModelCollection
{

    /**
     * @var \WP_Query
     */
    protected $query;

    /**
     * @param array $query
     *
     * @return Posts
     */
    public static function query(array $query = null)
    {
        return new static(new \WP_Query($query));
    }

    /**
     * Posts constructor.
     *
     * @param \WP_Query|null $query
     */
    public function __construct(\WP_Query $query = null)
    {
        global $wp_query;

        $this->query = $query ?? $wp_query;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->query->rewind_posts();
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->query->have_posts();
    }

    /**
     * @return Post
     */
    public function current()
    {
        $this->query->the_post();

        return new Post();
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->query->post->ID;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->query->post_count;
    }

}