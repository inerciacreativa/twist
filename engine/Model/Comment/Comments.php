<?php

namespace Twist\Model\Comment;

use Twist\Model\ModelCollection;
use Twist\Model\Post\Post;

/**
 * Class Comments
 *
 * @package Twist\Model\Comment
 */
class Comments extends ModelCollection
{

    /**
     * @var Query
     */
    protected $query;

    /**
     * @param Post $post
     *
     * @return Query
     */
    public static function from(Post $post): Query
    {
        return new Query($post);
    }

    /**
     * Comments constructor.
     *
     * @param Query   $query
     * @param Comment $parent
     */
    public function __construct(Query $query, Comment $parent = null)
    {
        parent::__construct($parent);

        $this->query = $query;
    }

    /**
     * @return Query
     */
    public function query(): Query
    {
        return $this->query;
    }

    /**
     * @return Post
     */
    public function post(): Post
    {
        return $this->query->post();
    }

    /**
     * @return Comment
     */
    public function current(): Comment
    {
        /** @var Comment $comment */
        $comment = parent::current();

        $GLOBALS['comment']       = &$comment;
        $GLOBALS['comment_depth'] = $comment->depth() + 1;

        return $comment;
    }

}