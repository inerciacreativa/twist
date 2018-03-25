<?php

namespace Twist\Model\Comment;

use Twist\Model\User\User;

/**
 * Class Author
 *
 * @package Twist\Model\Comment
 */
class Author extends User
{

    /**
     * @var Comment
     */
    protected $comment;

    /**
     * Author constructor.
     *
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        parent::__construct($comment->user_id());

        $this->setup($comment);
    }

    /**
     * @param Comment $comment
     */
    protected function setup(Comment $comment)
    {
        $this->comment = $comment;

        $properties = [
            'display_name' => 'comment_author',
            'user_email'   => 'comment_author_email',
            'user_url'     => 'comment_author_url',
            'user_ip'      => 'comment_author_IP',
        ];

        foreach ($properties as $property => $variable) {
            $value = $comment->object()->$variable;

            if ($property === 'display_name' && empty($value)) {
                $value = $this->exists() ? $this->user->display_name : __('Anonymous');
            } elseif ($property === 'user_url') {
                $value = ('http://' === $value) ? '' : esc_url($value, ['http', 'https']);
            }

            $this->user->$property = apply_filters('get_' . $variable, $value, $comment->id(), $comment->object());
        }
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return apply_filters('comment_author', parent::name(), $this->comment->id());
    }

    /**
     * @inheritdoc
     */
    public function email(): string
    {
        return apply_filters('author_email', parent::email(), $this->comment->id());
    }

    /**
     * @inheritdoc
     */
    public function url(): string
    {
        return esc_url(apply_filters('comment_url', parent::url(), $this->comment->id()));
    }

    /**
     * @return string
     */
    public function ip(): string
    {
        return esc_html($this->user->user_ip);
    }

}