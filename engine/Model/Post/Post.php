<?php

namespace Twist\Model\Post;

use Twist\Library\Model\ModelInterface;
use Twist\Library\Util\Macro;
use Twist\Model\Comment\Comments;
use Twist\Model\Comment\Query;
use Twist\Model\Model;

/**
 * Class Post
 *
 * @package Twist\Model\Post
 */
class Post extends Model implements ModelInterface
{

	use Macro;

	/**
	 * @var \WP_Post
	 */
	protected $post;

	/**
	 * @var Taxonomies
	 */
	protected $taxonomies;

	/**
	 * @var Author
	 */
	protected $author;

	/**
	 * @var PostMeta
	 */
	protected $meta;

	/**
	 * @var Query
	 */
	protected $comments;

	/**
	 * @var Thumbnail
	 */
	protected $thumbnail;

	/**
	 * Post constructor.
	 *
	 * @param \WP_Post|int|null $post
	 */
	public function __construct($post = null)
	{
		$this->post = get_post($post);
	}

	/**
	 * @return $this
	 *
	 * @throws \RuntimeException
	 */
	public function setup(): self
	{
		if (!setup_postdata($this->post)) {
			throw new \RuntimeException('There is no post data!');
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function reset(): self
	{
		wp_reset_postdata();

		return $this;
	}

	/**
	 * Retrieve the ID of the post.
	 *
	 * @return int
	 */
	public function id(): int
	{
		return (int) $this->post->ID;
	}

	/**
	 * @inheritdoc
	 */
	public function has_parent(): bool
	{
		return $this->post->post_parent && ($this->id() !== $this->post->post_parent);
	}

	/**
	 * @return Post|null
	 */
	public function parent(): ?Post
	{
		if ($this->parent === null && $this->has_parent()) {
			$this->parent = new static($this->post->post_parent);
		}

		return $this->parent;
	}

	/**
	 * @return bool
	 */
	public function has_thumbnail(): bool
	{
		return $this->thumbnail()->exists();
	}

	/**
	 * @return Thumbnail
	 */
	public function thumbnail(): Thumbnail
	{
		if ($this->thumbnail === null) {
			$this->thumbnail = new Thumbnail($this, true);
		}

		return $this->thumbnail;
	}

	/**
	 * @return bool
	 */
	public function has_format(): bool
	{
		return post_type_supports($this->type(), 'post-formats');
	}

	/**
	 * Retrieve the post format.
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	public function format(string $default = 'standard'): string
	{
		if (!$this->has_format()) {
			return '';
		}

		if (isset($this->taxonomies['post_format'])) {
			if (\is_int($this->taxonomies['post_format'])) {
				$format = get_the_terms($this->id(), 'post_format');
				$format = empty($format) ? $default : array_shift($format);

				$this->taxonomies['post_format'] = $format;
			} else {
				$format = $this->taxonomies['post_format'];
			}
		} else {
			$format = $default;
		}

		return "format-$format";
	}

	/**
	 * Retrieve the post type.
	 *
	 * @return string
	 */
	public function type(): string
	{
		return $this->post->post_type;
	}

	/**
	 * @return string
	 */
	public function type_name(): ?string
	{
		if ($type = get_post_type_object($this->type())) {
			return $type->labels->singular_name;
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public function has_password(): bool
	{
		return !empty($this->post->post_password);
	}

	/**
	 * @return bool
	 */
	public function is_password_required(): bool
	{
		return post_password_required($this->post);
	}

	/**
	 * @return bool
	 */
	public function is_sticky(): bool
	{
		return is_sticky($this->id());
	}

	/**
	 * @return bool
	 */
	public function is_draft(): bool
	{
		return \in_array($this->post->post_status, [
			'draft',
			'pending',
			'auto-draft',
		], false);
	}

	/**
	 * @return bool
	 */
	public function is_future(): bool
	{
		return $this->post->post_status === 'future';
	}

	/**
	 * @return string
	 */
	public function title(): string
	{
		return get_the_title($this->post);
	}

	/**
	 * @return string
	 */
	public function link(): string
	{
		return apply_filters('the_permalink', get_permalink($this->post));
	}

	/**
	 * @return string
	 */
	public function edit_link(): string
	{
		return get_edit_post_link($this->post);
	}

	/**
	 * @return string
	 */
	public function archive_link(): string
	{
		return get_post_type_archive_link($this->type());
	}

	/**
	 * Retrieve the date on which the post was written.
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function date(string $format = ''): string
	{
		$format = $format ?: (string) get_option('date_format');
		$date = mysql2date($format, $this->post->post_date);

		return apply_filters('get_the_date', $date, $format, $this->post);
	}

	/**
	 * Retrieve the date on which the post was written in ISO 8601 format.
	 *
	 * @see the_date()
	 *
	 * @return string
	 */
	public function published(): string
	{
		return $this->getDatetime($this->post->post_date, 'the_date');
	}

	/**
	 * @see the_modified_date()
	 *
	 * @return string
	 */
	public function modified(): string
	{
		return $this->getDatetime($this->post->post_modified, 'the_modified_date');
	}

	public function classes()
	{

		return post_class();
	}

	/**
	 * @param int $words
	 *
	 * @return string
	 */
	public function excerpt(int $words = 0): string
	{
		$filter = null;

		if ($words) {
			$filter = function () use ($words) {
				return $words;
			};

			add_filter('excerpt_length', $filter, 999);
		}

		$excerpt = apply_filters('the_excerpt', get_the_excerpt($this->post->ID));

		if ($filter !== null) {
			remove_filter('excerpt_length', $filter, 999);
		}

		return $excerpt;
	}

	/**
	 * @param string $more
	 *
	 * @return string
	 */
	public function content(string $more = null): string
	{
		ob_start();
		the_content($more);

		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public function raw_content(): string
	{
		return $this->post->post_content;
	}

	/**
	 * Retrieve the post status.
	 *
	 * @return string
	 */
	public function status(): string
	{
		$status = $this->post->post_status;

		if ($this->type() === 'attachment') {
			if ($status === 'private') {
				return $status;
			}

			if ($this->has_parent() && ($parent = $this->parent())) {
				$status = $parent->status();

				if ($status === 'trash') {
					return $parent->meta()->get('_wp_trash_meta_status');
				}

				return $status;
			}

			if ($status === 'inherit') {
				return 'publish';
			}
		}

		return apply_filters('get_post_status', $status, $this->post);
	}

	/**
	 * @return Author
	 */
	public function author(): Author
	{
		if ($this->author === null) {
			$this->author = new Author($this->post->post_author);
		}

		return $this->author;
	}

	/**
	 * @return Query
	 */
	public function comments(): Query
	{
		if ($this->comments === null) {
			$this->comments = Comments::from($this);
		}

		return $this->comments;
	}

	/**
	 * @return Taxonomies
	 */
	public function taxonomies(): Taxonomies
	{
		if ($this->taxonomies === null) {
			$this->taxonomies = new Taxonomies($this);
		}

		return $this->taxonomies;
	}

	/**
	 * @return Terms
	 */
	public function categories(): ?Terms
	{
		return $this->taxonomies()['category'];
	}

	/**
	 * @return Terms
	 */
	public function tags(): ?Terms
	{
		return $this->taxonomies()['post_tag'];
	}

	/**
	 * @return PostMeta
	 */
	public function meta(): PostMeta
	{
		if ($this->meta === null) {
			$this->meta = new PostMeta($this);
		}

		return $this->meta;
	}

	/**
	 * @param string $field
	 *
	 * @return mixed|null
	 */
	public function field(string $field)
	{
		return $this->post->$field ?? null;
	}

	/**
	 * @return null|\WP_Post
	 */
	public function object(): ?\WP_Post
	{
		return $this->post;
	}

	/**
	 * @param string $date
	 * @param string $filter
	 *
	 * @return string
	 */
	protected function getDatetime(string $date, string $filter): string
	{
		$format = 'c';
		$date = (string) date($format, strtotime($date));
		$date = (string) apply_filters("get_$filter", $date, $format, $this->post);

		return apply_filters($filter, $date, $format, '', '');
	}

}