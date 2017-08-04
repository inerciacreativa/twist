<?php

namespace Twist\Model\Post;

use Twist\Model\User\User;

/**
 * Class Author
 *
 * @package Twist\Model\Post
 */
class Author extends User
{

    /**
     * Author constructor.
     *
     * @param int $user
     *
     * @global \WP_User $authordata
     */
    public function __construct($user = 0)
    {
        global $authordata;

        $user = $user ?: $authordata;

        parent::__construct($user);
    }

    /**
     * @return string
     */
    public function link(): string
    {
        return get_author_posts_url($this->user->ID, parent::nice_name());
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return apply_filters('the_author', parent::name());
    }

    /**
     * @inheritdoc
     */
    public function first_name(): string
    {
        return $this->filter('first_name');
    }

    /**
     * @inheritdoc
     */
    public function last_name(): string
    {
        return $this->filter('last_name');
    }

    /**
     * @inheritdoc
     */
    public function email(): string
    {
        return $this->filter('user_email');
    }

    /**
     * @inheritdoc
     */
    public function url(): string
    {
        return esc_url($this->filter('user_url'));
    }

    /**
     * @inheritdoc
     */
    public function description(): string
    {
        return $this->filter('description');
    }

    /**
     * @param string $field
     *
     * @return string
     */
    protected function filter($field): string
    {
        $value = apply_filters("get_the_author_$field", $this->user->$field, $this->user->ID);

        return apply_filters("the_author_$field", $value, $this->user->ID);
    }

}