<?php

namespace Twist\Model\Post;

use Twist\Model\ModelArray;

/**
 * Class Metas
 *
 * @package Twist\Model\Post
 */
class Metas extends ModelArray
{

    /**
     * Metas constructor.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        parent::__construct(get_metadata('post', $post->id()), $post);
    }

    /**
     * @param string $name
     *
     * @return array|mixed
     */
    public function offsetGet($name)
    {
        $value = parent::offsetGet($name);

        if (!is_array($value)) {
            return null;
        }

        return count($value) === 1 ? maybe_unserialize($value[0]) : array_map('maybe_unserialize', $value);
    }

}