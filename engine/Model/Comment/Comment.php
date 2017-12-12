<?php

namespace Twist\Model\Comment;

use Twist\Model\Model;
use Twist\Model\Post\Post;
use Twist\Model\User\User;

/**
 * Class Comment
 *
 * @package Twist\Model\Comment
 */
class Comment extends Model
{

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var \WP_Comment
     */
    protected $comment;

    /**
     * @var int
     */
    protected $depth;

    /**
     * @var Author
     */
    protected $author;

    /**
     * Comment constructor.
     *
     * @param Comments    $comments
     * @param \WP_Comment $comment
     * @param int         $depth
     */
    public function __construct(Comments $comments, \WP_Comment $comment, int $depth = 0)
    {
        $this->query   = $comments->query();
        $this->comment = $comment;
        $this->depth   = $depth;

        if ($comments->has_parent()) {
            $this->setParent($comments->parent());
        }
    }

    /**
     * @return Comments
     */
    protected function setChildren(): Comments
    {
        return new Comments($this->query, $this);
    }

    /**
     * Returns the ID of this comment.
     *
     * @return int
     */
    public function id(): int
    {
        return (int)$this->comment->comment_ID;
    }

    /**
     * Returns the ID of the post.
     *
     * @return int
     */
    public function post_id(): int
    {
        return (int)$this->comment->comment_post_ID;
    }

    /**
     * @return int
     */
    public function user_id(): int
    {
        return (int)$this->comment->user_id;
    }

    /**
     * @return Post
     */
    public function post(): Post
    {
        return $this->query->post();
    }

    /**
     * @return int
     */
    public function depth(): int
    {
        return (int)$this->depth;
    }

    /**
     * @return bool
     */
    public function is_approved(): bool
    {
        return (bool)(int)$this->comment->comment_approved;
    }

	/**
	 * Returns whether this comment is a user comment.
	 *
	 * @return bool
	 */
    public function is_comment(): bool
    {
	    return $this->type() === 'comment';
    }

    /**
     * Returns whether this comment is a ping.
     *
     * @return bool
     */
    public function is_ping(): bool
    {
        return !$this->is_comment();
    }

    /**
     * @return bool
     */
    public function has_replies(): bool
    {
        return $this->has_children();
    }

    /**
     * @return Comments
     */
    public function replies(): Comments
    {
        return $this->children();
    }

    /**
     * Returns the HTML classes.
     *
     * @return string
     */
    public function classes(): string
    {
        $classes = [$this->type()];

        if ($this->has_children()) {
            $classes[] = 'parent';
        }

        if ($this->has_parent()) {
            $classes[] = 'children';
        }

        if ($this->author()->exists()) {
            $classes[] = 'by-user';
            $classes[] = 'by-author-' . sanitize_html_class($this->author()->name(), $this->author()->id());

            if ($this->author()->id() === $this->post()->author()->id()) {
                $classes[] = 'by-post-author';
            }
        }

        return implode(' ', get_comment_class($classes));
    }

    /**
     * Returns the text of this comment.
     *
     * @return string
     */
    public function content(): string
    {
        $content = apply_filters('get_comment_text', $this->comment->comment_content, $this->comment);
        $content = apply_filters('comment_text', $content, $this->comment);

        return $content;
    }

    /**
     * Retrieve comment date.
     *
     * @param string $format
     *
     * @return string
     */
    public function date(string $format = ''): string
    {
        $format = $format ?: (string)get_option('date_format');
        $date   = mysql2date($format, $this->comment->comment_date);

        return apply_filters('get_comment_date', $date, $format, $this->comment);
    }

    /**
     * Retrieve the comment time.
     *
     * @param string $format
     *
     * @return string
     */
    public function time(string $format = ''): string
    {
        $format = $format ?: (string)get_option('time_format');
        $time   = mysql2date($format, $this->comment->comment_date);

        return apply_filters('get_comment_time', $time, $format, false, true, $this->comment);
    }

    /**
     * Retrieve the comment date in ISO 8601 format.
     *
     * @return string
     */
    public function datetime(): string
    {
        $format = 'c';
        $date   = date($format, strtotime($this->comment->comment_date));

        return apply_filters('get_the_date', $date, $format, $this->comment);
    }

    /**
     * Returns the type of this comment.
     *
     * @return string
     */
    public function type(): string
    {
        $type = empty($this->comment->comment_type) ? 'comment' : $this->comment->comment_type;

        return apply_filters('get_comment_type', $type);
    }

    /**
     * Returns the permalink of this comment.
     *
     * @return string
     */
    public function link(): string
    {
        return get_comment_link($this->comment);
    }

    /**
     * @return string
     */
    public function edit_link(): string
    {
        $link = '';

        if (User::current()->can('edit_comment', $this->id())) {
            $link = admin_url('comment.php?action=editcomment&amp;c=') . $this->id();
            $link = esc_url(apply_filters('get_edit_comment_link', $link));
        }

        return $link;
    }

    /**
     * Returns the reply link for this comment.
     *
     * @param string $respond
     *
     * @return string
     */
    public function reply_link($respond = '#respond'): string
    {
        if (!$this->can_be_replied()) {
            return '';
        }

        if (User::current()->can('comment')) {
            $link = add_query_arg('replytocom', $this->id(), $this->post()->link()) . $respond;
        } else {
            $link = wp_login_url($this->post()->link());
        }

        return esc_url($link);
    }

    /**
     * Whether this comment can be replied.
     *
     * @return bool
     */
    public function can_be_replied(): bool
    {
        if (!$this->query->are_open()) {
            return false;
        }

        if ($this->query->max_depth() <= $this->depth) {
            return false;
        }

        return true;
    }

    /**
     * Returns the author of this comment.
     *
     * @return Author
     */
    public function author(): Author
    {
        if ($this->author === null) {
            $this->author = new Author($this);
        }

        return $this->author;
    }

    /**
     * @return \WP_Comment
     */
    public function object(): \WP_Comment
    {
        return $this->comment;
    }

}