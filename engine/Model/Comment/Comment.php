<?php

namespace Twist\Model\Comment;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Classes;
use Twist\Library\Support\Arr;
use Twist\Library\Support\Str;
use Twist\Model\CollectionInterface;
use Twist\Model\Model;
use Twist\Model\Post\Post;
use Twist\Model\Site\Site;
use Twist\Model\User\User;
use WP_Comment;

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
	private $query;

	/**
	 * @var WP_Comment
	 */
	private $comment;

	/**
	 * @var int
	 */
	private $depth;

	/**
	 * @var Author
	 */
	private $author;

	/**
	 * @var Meta
	 */
	private $meta;

	/**
	 * Comment constructor.
	 *
	 * @param Comments   $comments
	 * @param WP_Comment $comment
	 * @param int        $depth
	 */
	public function __construct(Comments $comments, WP_Comment $comment, int $depth = 0)
	{
		$this->query   = $comments->query();
		$this->comment = $comment;
		$this->depth   = $depth;

		if ($comments->has_parent()) {
			$this->set_parent($comments->parent());
		}
	}

	/**
	 * @inheritdoc
	 */
	public function children(): ?CollectionInterface
	{
		if ($this->children === null) {
			$this->set_children(new Comments($this->query, $this));
		}

		return $this->children;
	}

	/**
	 * Returns the ID of this comment.
	 *
	 * @return int
	 */
	public function id(): int
	{
		return (int) $this->comment->comment_ID;
	}

	/**
	 * Returns the ID of the post.
	 *
	 * @return int
	 */
	public function post_id(): int
	{
		return (int) $this->comment->comment_post_ID;
	}

	/**
	 * @return int
	 */
	public function user_id(): int
	{
		return (int) $this->comment->user_id;
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
		return $this->depth + 1;
	}

	/**
	 * @return bool
	 */
	public function is_approved(): bool
	{
		return (bool) (int) $this->comment->comment_approved;
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
	public function replies(): ?CollectionInterface
	{
		return $this->children();
	}

	/**
	 * Returns the HTML classes.
	 *
	 * @param string|array $class
	 *
	 * @return Classes
	 */
	public function classes($class = []): Classes
	{
		$classes   = Classes::parse($class);
		$classes[] = $this->type();

		if ($this->has_parent()) {
			$classes[] = 'has-parent';
		}

		if ($this->has_children()) {
			$classes[] = 'has-children';

			if ($this->depth() === 1) {
				$classes[] = 'is-thread';
			}
		}

		if ($this->author()->exists()) {
			$author = Str::slug($this->author()->name());
			$author = Classes::sanitize($author, $this->author()->id());

			$classes[] = 'by-user';
			$classes[] = 'by-author-' . $author;

			if ($this->author()->id() === $this->post()->author()->id()) {
				$classes[] = 'by-post-author';
			}
		}

		$classes = Hook::apply('comment_class', $classes, $class, $this->id(), $this->object(), $this->post()
																									 ->id());

		return Classes::make($classes);
	}

	/**
	 * Returns the text of this comment.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function content(array $options = []): string
	{
		$options = Arr::defaults([
			'filter' => true,
		], $options);

		$content = Hook::apply('get_comment_text', $this->comment->comment_content, $this->comment);
		$content = Hook::apply('comment_text', $content, $this->comment);

		if ($options['filter']) {
			$document = new Document(Site::language());
			$document->loadMarkup(Str::whitespace($content));
			$document = Hook::apply('twist_comment_content', $document, $this);

			$content = $document->saveMarkup();
		}

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
		$format = $format ?: (string) get_option('date_format');
		$date   = mysql2date($format, $this->comment->comment_date);

		return Hook::apply('get_comment_date', $date, $format, $this->comment);
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
		$format = $format ?: (string) get_option('time_format');
		$time   = mysql2date($format, $this->comment->comment_date);

		return Hook::apply('get_comment_time', $time, $format, false, true, $this->comment);
	}

	/**
	 * Retrieve the comment date in ISO 8601 format.
	 *
	 * @return string
	 */
	public function published(): string
	{
		$format = 'c';
		$date   = date($format, strtotime($this->comment->comment_date));

		return Hook::apply('get_the_date', $date, $format, $this->comment);
	}

	/**
	 * Returns the type of this comment.
	 *
	 * @return string
	 */
	public function type(): string
	{
		$type = empty($this->comment->comment_type) ? 'comment' : $this->comment->comment_type;

		return Hook::apply('get_comment_type', $type);
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
	public function edit_link(): ?string
	{
		return get_edit_comment_link($this->comment);
	}

	/**
	 * Returns the reply link for this comment.
	 *
	 * @param string $respond
	 *
	 * @return string
	 */
	public function reply_link($respond = '#respond'): ?string
	{
		if (!$this->can_be_replied()) {
			return null;
		}

		if (User::current()->can('comment')) {
			$link = add_query_arg('replytocom', $this->id(), $this->post()
																  ->link()) . $respond;
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
		return $this->author ?? $this->author = new Author($this);
	}

	/**
	 * @return Meta
	 */
	public function meta(): Meta
	{
		return $this->meta ?? $this->meta = new Meta($this);
	}

	/**
	 * @return WP_Comment
	 */
	public function object(): WP_Comment
	{
		return $this->comment;
	}

}
