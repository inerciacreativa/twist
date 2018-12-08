<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Library\Hook\Hook;
use Twist\Library\Model\CollectionInterface;
use Twist\Library\Model\Model;
use Twist\Library\Model\ModelInterface;
use Twist\Library\Util\Macroable;
use Twist\Model\Comment\Query as CommentQuery;
use Twist\Model\Image\Image;
use Twist\Model\Image\Images;

/**
 * Class Post
 *
 * @package Twist\Model\Post
 */
class Post extends Model
{

	use Macroable;

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
	 * @var Meta
	 */
	protected $meta;

	/**
	 * @var CommentQuery
	 */
	protected $comments;

	/**
	 * @var Images
	 */
	protected $images;

	/**
	 * @var Image
	 */
	protected $thumbnail;

	/**
	 * @param \WP_Post|int $post
	 *
	 * @return Post
	 * @throws AppException
	 */
	public static function make($post): Post
	{
		return new static($post);
	}

	/**
	 * Post constructor.
	 *
	 * @param \WP_Post|int|null $post
	 * @throws AppException
	 */
	public function __construct($post = null)
	{
		$this->post = get_post($post);

		if ($this->post === null) {
			new AppException(sprintf('<p>Not valid post data.</p><pre>%s</pre>', print_r($post, true)));
		}
	}

	/**
	 * @return $this
	 * @throws AppException
	 */
	public function setup(): self
	{
		if (!setup_postdata($this->post)) {
			new AppException('There is no active WP_Query instance');
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
	 * @return int
	 */
	public function parent_id(): int
	{
		return (int) $this->post->post_parent;
	}

	/**
	 * @return Post|null
	 * @throws AppException
	 */
	public function parent(): ?ModelInterface
	{
		if ($this->parent === null && $this->has_parent()) {
			$this->set_parent(static::make($this->post->post_parent));
		}

		return $this->parent;
	}

	/**
	 * @inheritdoc
	 * @throws AppException
	 */
	public function has_children(): bool
	{
		$type = get_post_type_object($this->type());

		if (!$type || !$type->hierarchical) {
			return false;
		}

		$query = Query::make([
			'post_parent'    => $this->id(),
			'post_type'      => $this->type(),
			'post_status'    => 'any',
			'posts_per_page' => -1,
		]);

		if ($query->count() > 0) {
			$this->set_children($query->posts());

			return true;
		}

		return false;
	}

	/**
	 * @return Posts|null
	 * @throws AppException
	 */
	public function children(): ?CollectionInterface
	{
		if ($this->has_children()) {
			return $this->children;
		}

		return null;
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
	 * @param string $prefix
	 * @param string $default
	 *
	 * @return string
	 * @throws AppException
	 */
	public function format(string $prefix = 'format', string $default = 'standard'): string
	{
		if (!$this->has_format()) {
			return '';
		}

		$format = $default;

		if ($this->taxonomies()
		         ->has('post_format') && ($terms = $this->taxonomies()
		                                                ->get('post_format')) && ($terms->count() > 0) && ($term = $terms->first())) {
			$format = str_replace('post-format-', '', $term->slug());
		}

		return "$prefix-$format";
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
	 * @param bool $attribute
	 *
	 * @return string
	 */
	public function title(bool $attribute = false): string
	{
		if ($attribute) {
			return html_entity_decode(the_title_attribute([
				'echo' => false,
				'post' => $this->post,
			]), ENT_HTML5 | ENT_QUOTES);
		}

		return get_the_title($this->post);
	}

	/**
	 * @return string
	 */
	public function link(): string
	{
		return Hook::apply('the_permalink', get_permalink($this->post));
	}

	/**
	 * @return string
	 */
	public function edit_link(): ?string
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
		$date   = mysql2date($format, $this->post->post_date);

		return Hook::apply('get_the_date', $date, $format, $this->post);
	}

	/**
	 * Retrieve the date on which the post was written.
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function time(string $format = ''): string
	{
		$format = $format ?: (string) get_option('time_format');
		$time   = mysql2date($format, $this->post->post_date);

		return Hook::apply('get_the_time', $time, $format, $this->post);
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
		return $this->datetime($this->post->post_date, 'the_date');
	}

	/**
	 * @see the_modified_date()
	 *
	 * @return string
	 */
	public function modified(): string
	{
		return $this->datetime($this->post->post_modified, 'the_modified_date');
	}

	/**
	 * @param string|array $class
	 *
	 * @return string
	 * @throws AppException
	 */
	public function classes($class = ''): string
	{
		$classes = [];

		if ($class) {
			if (\is_string($class)) {
				$class = (array) preg_split('#\s+#', $class);
			}

			$classes = (array) $class;
		}

		$classes[] = $this->type();

		if ($this->has_format()) {
			$classes[] = $this->format('is');
		}

		if ($this->has_thumbnail()) {
			$classes[] = 'has-thumbnail';
		}

		$classes = Hook::apply('post_class', $classes, $class, $this->id());

		return implode(' ', array_unique($classes));
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

		$excerpt = Hook::apply('the_excerpt', get_the_excerpt($this->post->ID));

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
	 *
	 * @throws AppException
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

		return Hook::apply('get_post_status', $status, $this->post);
	}

	/**
	 * @return int
	 */
	public function thumbnail_id(): int
	{
		return (int) $this->meta()->get('_thumbnail_id');
	}

	/**
	 * @return bool
	 */
	public function has_thumbnail(): bool
	{
		return $this->thumbnail_id() > 0;
	}

	/**
	 * @return Image
	 * @throws AppException
	 */
	public function thumbnail(): Image
	{
		if ($this->thumbnail === null && $this->has_thumbnail()) {
			$this->thumbnail = new Image($this->thumbnail_id(), $this);
		}

		return $this->thumbnail;
	}

	/**
	 * @return Images
	 * @throws AppException
	 */
	public function images(): Images
	{
		if ($this->images === null) {
			$this->images = Images::make($this);
		}

		return $this->images;
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
	 * @return CommentQuery
	 * @throws AppException
	 */
	public function comments(): CommentQuery
	{
		if ($this->comments === null) {
			$this->comments = new CommentQuery($this);
		}

		return $this->comments;
	}

	/**
	 * @return bool
	 */
	public function has_comments(): bool
	{
		return $this->comment_count() > 0;
	}

	/**
	 * @return int
	 */
	public function comment_count(): int
	{
		return (int) Hook::apply('get_comments_number', $this->post->comment_count, $this->id());
	}

	/**
	 * @return bool
	 */
	public function can_be_commented(): bool
	{
		return (bool) Hook::apply('comments_open', $this->post->comment_status === 'open', $this->id());
	}

	/**
	 * @return bool
	 */
	public function can_be_pinged(): bool
	{
		return (bool) Hook::apply('pings_open', $this->post->ping_status === 'open', $this->id());
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
	 * @throws AppException
	 */
	public function categories(): ?Terms
	{
		return $this->taxonomies()->get('category');
	}

	/**
	 * @return Terms
	 * @throws AppException
	 */
	public function tags(): ?Terms
	{
		return $this->taxonomies()->get('post_tag');
	}

	/**
	 * @return Meta
	 */
	public function meta(): Meta
	{
		if ($this->meta === null) {
			$this->meta = new Meta($this);
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
	protected function datetime(string $date, string $filter): string
	{
		$format = 'c';
		$date   = (string) date($format, strtotime($date));
		$date   = (string) Hook::apply("get_$filter", $date, $format, $this->post);

		return Hook::apply($filter, $date, $format, '', '');
	}

}