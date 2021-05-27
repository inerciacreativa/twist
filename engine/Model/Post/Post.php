<?php

namespace Twist\Model\Post;

use Twist\App\AppException;
use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Classes;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Arr;
use Twist\Library\Support\Macroable;
use Twist\Library\Support\Str;
use Twist\Model\CollectionInterface;
use Twist\Model\Comment\CommentsQuery;
use Twist\Model\HasChildren;
use Twist\Model\HasChildrenInterface;
use Twist\Model\HasParent;
use Twist\Model\HasParentInterface;
use Twist\Model\Image\Image;
use Twist\Model\Image\Images;
use Twist\Model\ModelInterface;
use Twist\Model\Site\Site;
use Twist\Model\Taxonomy\Term;
use Twist\Model\Taxonomy\Terms;
use WP_Post;

/**
 * Class Post
 *
 * @package Twist\Model\Post
 */
class Post implements ModelInterface, HasParentInterface, HasChildrenInterface
{

	use HasParent;

	use HasChildren;

	use Macroable;

	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * @var PostTaxonomies
	 */
	private $taxonomies;

	/**
	 * @var PostAuthor
	 */
	private $author;

	/**
	 * @var PostMeta
	 */
	private $meta;

	/**
	 * @var CommentsQuery
	 */
	private $comments;

	/**
	 * @var Images
	 */
	private $images;

	/**
	 * @var Image
	 */
	private $thumbnail;

	/**
	 * @var bool
	 */
	private $has_children;

	/**
	 * @param string            $url
	 * @param string|array|null $type
	 *
	 * @return static|null
	 *
	 * @throws AppException
	 */
	public static function by_path(string $url, $type = null): ?self
	{
		if (empty($type)) {
			$type = 'page';
		}

		$post = get_page_by_path($url, OBJECT, $type);
		if (!($post instanceof WP_Post)) {
			return null;
		}

		return new static($post);
	}

	/**
	 * @param string            $title
	 * @param string|array|null $type
	 *
	 * @return static|null
	 *
	 * @throws AppException
	 */
	public static function by_title(string $title, $type = null): ?self
	{
		if (empty($type)) {
			$type = 'page';
		}

		$post = get_page_by_title($title, OBJECT, $type);
		if (!($post instanceof WP_Post)) {
			return null;
		}

		return new static($post);
	}

	/**
	 * @param int $post
	 *
	 * @return bool
	 */
	public static function exists_id(int $post): bool
	{
		global $wpdb;

		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID = %d", $post);

		return (bool) $wpdb->get_var($query);
	}

	/**
	 * @param WP_Post|int $post
	 *
	 * @return Post
	 * @throws AppException
	 */
	public static function make($post): self
	{
		return new static($post);
	}

	/**
	 * Post constructor.
	 *
	 * @param WP_Post|int|null $post
	 *
	 * @throws AppException
	 */
	public function __construct($post = null)
	{
		$this->post = get_post($post);

		if (!($this->post instanceof WP_Post)) {
			new AppException(sprintf('<p>Not valid post data.</p><pre>%s</pre>', print_r($post, true)));
		}
	}

	/**
	 * @return $this
	 */
	public function setup(): self
	{
		global $id;

		if ($id !== $this->post->ID && !setup_postdata($this->post)) {
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
	 * @return string
	 */
	public function name(): string
	{
		return $this->post->post_name;
	}

	/**
	 * @inheritDoc
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
	 *
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
	 * @inheritDoc
	 */
	public function has_children(): bool
	{
		if ($this->has_children !== null) {
			return $this->has_children;
		}

		$type = $this->type(true);

		if (!$type || !$type->hierarchical) {
			$this->has_children = false;
		} else {
			$this->has_children = PostsQuery::has_children($this);
		}

		return $this->has_children;
	}

	/**
	 * @return Posts|null
	 */
	public function children(): ?CollectionInterface
	{
		if ($this->children === null && $this->has_children()) {
			$this->set_children(PostsQuery::children($this));
		}

		return $this->children;
	}

	/**
	 * @param string $taxonomy
	 * @param array  $exclude
	 *
	 * @return Post|null
	 */
	public function prev(string $taxonomy = '', array $exclude = []): ?Post
	{
		return $this->getAdjacentPost(true, $taxonomy, $exclude);
	}

	/**
	 * @param string $taxonomy
	 * @param array  $exclude
	 *
	 * @return Post|null
	 */
	public function next(string $taxonomy = '', array $exclude = []): ?Post
	{
		return $this->getAdjacentPost(false, $taxonomy, $exclude);
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
	 * Retrieve the permalink for this post type archive.
	 *
	 * @return string
	 */
	public function archive_link(): string
	{
		return get_post_type_archive_link($this->type());
	}

	/**
	 * @param string $taxonomy
	 * @param array  $exclude
	 *
	 * @return string|null
	 */
	public function prev_link(string $taxonomy = '', array $exclude = []): ?string
	{
		if ($post = $this->prev($taxonomy, $exclude)) {
			return $post->link();
		}

		return null;
	}

	/**
	 * @param string $taxonomy
	 * @param array  $exclude
	 *
	 * @return string|null
	 */
	public function next_link(string $taxonomy = '', array $exclude = []): ?string
	{
		if ($post = $this->next($taxonomy, $exclude)) {
			return $post->link();
		}

		return null;
	}

	/**
	 * Retrieve the date on which the post was written.
	 *
	 * @param string|null $format
	 *
	 * @return string
	 */
	public function date(string $format = null): string
	{
		return $this->getDatetime($format);
	}

	/**
	 * Retrieve the date on which the post was written.
	 *
	 * @param string|null $format
	 *
	 * @return string
	 */
	public function time(string $format = null): string
	{
		return $this->getDatetime($format, 'time');
	}

	/**
	 * @return string
	 */
	public function datetime(): string
	{
		return $this->getDatetime('U');
	}

	/**
	 * @return string
	 */
	public function time_ago(): string
	{
		return human_time_diff($this->getDatetime('U'));
	}

	/**
	 * Retrieve the date on which the post was written in ISO 8601 format.
	 *
	 * @return string
	 * @see the_date()
	 *
	 */
	public function published(): string
	{
		return $this->getISO8601Datetime($this->post->post_date, 'the_date');
	}

	/**
	 * @return string
	 * @see the_modified_date()
	 *
	 */
	public function modified(): string
	{
		return $this->getISO8601Datetime($this->post->post_modified, 'the_modified_date');
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
	 * @return bool
	 */
	public function has_excerpt(): bool
	{
		return !empty($this->post->post_excerpt);
	}

	/**
	 * @param int $words
	 *
	 * @return string
	 */
	public function excerpt(int $words = 55): string
	{
		if ($this->is_password_required()) {
			return __('There is no excerpt because this is a protected post.');
		}

		if ($this->has_excerpt()) {
			$excerpt = $this->post->post_excerpt;
		} else {
			$excerpt = $this->getContent('');
			$excerpt = Hook::apply('twist_post_excerpt_raw', $excerpt, $this);

			$excerpt = strip_shortcodes($excerpt);
			if (function_exists('excerpt_remove_blocks')) {
				$excerpt = excerpt_remove_blocks($excerpt);
			}

			$excerpt = Hook::apply('the_content', $excerpt);
			$excerpt = str_replace(']]>', ']]&gt;', $excerpt);

			$words = Hook::apply('excerpt_length', $words);
			$more  = Hook::apply('excerpt_more', ' [&hellip;]');

			$excerpt = Hook::apply('twist_post_excerpt_before', $excerpt, $this);
			$excerpt = Str::stripTags($excerpt, ['figure']);
			$excerpt = Str::whitespace($excerpt);
			$excerpt = Str::words($excerpt, $words, $more);
			$excerpt = Hook::apply('twist_post_excerpt_after', $excerpt, $this);
		}

		$excerpt = Hook::apply('wp_trim_excerpt', $excerpt, $this->post->post_excerpt);
		$excerpt = Hook::apply('the_excerpt', $excerpt);

		return $excerpt;
	}

	/**
	 * @param array $options
	 *   [
	 *   'more_link' => (Tag|string|null)
	 *   'strip_teaser' => (bool)
	 *   'filter' => (bool)
	 *   ]
	 *
	 * @return string
	 */
	public function content(array $options = []): string
	{
		$options = Arr::defaults([
			'more_link'    => null,
			'strip_teaser' => false,
			'filter'       => true,
		], $options);

		$content = $this->getContent($options['more_link'], $options['strip_teaser']);
		$content = $this->preFixContentEntities($content);
		$content = Hook::apply('the_content', $content);
		$content = $this->postFixContentEntities($content);

		if ($options['filter']) {
			$document = Hook::apply('twist_post_content', $this->getDocument($content), $this);
			$content  = $document->saveMarkup();
		}

		$content = str_replace(']]>', ']]&gt;', $content);

		return $content;
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	private function preFixContentEntities(string $content): string
	{
		return str_replace(['&lt;', '&#60;'], '%%less_than%%', $content);
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	private function postFixContentEntities(string $content): string
	{
		return str_replace('%%less_than%%', '&lt;', $content);
	}

	/**
	 * @return Document
	 */
	public function document(): Document
	{
		$content = $this->content(['filter' => false]);

		return $this->getDocument($content);
	}

	/**
	 * @return PostMeta
	 */
	public function meta(): PostMeta
	{
		return $this->meta ?? $this->meta = new PostMeta($this);
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
	public function thumbnail(): ?Image
	{
		if ($this->thumbnail === null && $this->has_thumbnail()) {
			$this->thumbnail = new Image($this->thumbnail_id(), $this);
		}

		return $this->thumbnail;
	}

	/**
	 * @return int
	 */
	public function thumbnail_id(): int
	{
		return (int) $this->meta()->get('_thumbnail_id');
	}

	/**
	 * @return Images
	 */
	public function images(): Images
	{
		return $this->images ?? $this->images = Images::make($this);
	}

	/**
	 * Retrieve the post {@see Author} object.
	 *
	 * @return PostAuthor
	 */
	public function author(): PostAuthor
	{
		return $this->author ?? $this->author = new PostAuthor((int) $this->post->post_author, $this);
	}

	/**
	 * @return PostTaxonomies
	 */
	public function taxonomies(): PostTaxonomies
	{
		return $this->taxonomies ?? $this->taxonomies = new PostTaxonomies($this);
	}

	/**
	 * @return Terms
	 */
	public function categories(): ?Terms
	{
		return $this->taxonomies()->get('category');
	}

	/**
	 * @return Terms
	 */
	public function tags(): ?Terms
	{
		return $this->taxonomies()->get('post_tag');
	}

	/**
	 * @return bool
	 */
	public function has_comments(): bool
	{
		return $this->comment_count() > 0;
	}

	/**
	 * @return CommentsQuery
	 */
	public function comments(): CommentsQuery
	{
		return $this->comments ?? $this->comments = new CommentsQuery($this);
	}

	/**
	 * @return int
	 */
	public function comment_count(): int
	{
		return (int) Hook::apply('get_comments_number', $this->post->comment_count, $this->id());
	}

	/**
	 * Determines whether the current post is open for comments.
	 *
	 * @return bool
	 */
	public function can_be_commented(): bool
	{
		return (bool) Hook::apply('comments_open', $this->post->comment_status === 'open', $this->id());
	}

	/**
	 * Determines whether the current post is open for pings.
	 *
	 * @return bool
	 */
	public function can_be_pinged(): bool
	{
		return (bool) Hook::apply('pings_open', $this->post->ping_status === 'open', $this->id());
	}

	/**
	 * @param string|array $class
	 *
	 * @return Classes
	 */
	public function classes($class = []): Classes
	{
		$classes   = Classes::parse($class);
		$classes[] = $this->type();

		if ($this->has_format()) {
			$classes[] = $this->format('is');
		}

		if ($this->has_thumbnail()) {
			$classes[] = 'has-thumbnail';
		}

		$classes = Hook::apply('post_class', $classes, $class, $this->id());

		return Classes::make($classes);
	}

	/**
	 * Retrieve the post type.
	 *
	 * @param bool $object
	 *
	 * @return string|object
	 */
	public function type(bool $object = false)
	{
		return $object ? get_post_type_object($this->post->post_type) : $this->post->post_type;
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

			try {
				if ($this->has_parent() && ($parent = $this->parent())) {
					$status = $parent->status();

					if ($status === 'trash') {
						return $parent->meta()->get('_wp_trash_meta_status');
					}

					return $status;
				}
			} catch (AppException $exception) {
				$status = 'unknown';
			}

			if ($status === 'inherit') {
				return 'publish';
			}
		}

		return Hook::apply('get_post_status', $status, $this->post);
	}

	/**
	 * @param bool $prefix
	 *
	 * @return string|null
	 */
	public function mime_type(bool $prefix = false): ?string
	{
		$mime = $this->post->post_mime_type;

		if (empty($mime)) {
			return null;
		}

		if (!$prefix) {
			$mime = str_replace([
				'application/',
				'image/',
				'text/',
				'audio/',
				'video/',
				'music/',
			], '', $mime);
		}

		return $mime;
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
	 */
	public function format(string $prefix = 'format', string $default = 'standard'): string
	{
		if (!$this->has_format()) {
			return '';
		}

		$format = $default;

		/** @var Term $term */
		if (($terms = $this->taxonomies()
						   ->get('post_format')) && ($term = $terms->first())) {
			$format = str_replace('post-format-', '', $term->slug());
		}

		return "$prefix-$format";
	}

	/**
	 * @return bool
	 */
	public function has_template(): bool
	{
		return (bool) $this->template();
	}

	/**
	 * @return string|null
	 */
	public function template(): ?string
	{
		$template = $this->meta()->get('_wp_page_template');

		return (!$template || $template === 'default') ? null : $template;
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
		return in_array($this->post->post_status, [
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
	 * @return bool
	 */
	public function is_preview(): bool
	{
		try {
			return PostsQuery::main()->is_preview();
		} catch (AppException $exception) {
			return false;
		}
	}

	/**
	 * @return WP_Post
	 */
	public function object(): WP_Post
	{
		return $this->post;
	}

	/**
	 * @param string|null $more_link
	 * @param bool        $strip_teaser
	 *
	 * @return string
	 */
	private function getContent(string $more_link = null, bool $strip_teaser = false): string
	{
		global $page, $more, $preview, $pages, $multipage;

		if ($this->is_password_required()) {
			return $this->getPasswordForm();
		}

		try {
			$this->setup();
		} catch (AppException $exception) {
			return '';
		}

		if ($more_link === null) {
			$more = Tag::span([
				'aria-label' => sprintf(__('Continue reading %s'), $this->title(true)),
			], __('(more&hellip;)'));
		}

		$output     = '';
		$has_teaser = false;

		if ($page > count($pages)) {
			$page = count($pages);
		}

		$content = $pages[$page - 1];
		if (preg_match('/<!--more(.*?)?-->/', $content, $matches)) {
			$content = explode($matches[0], $content, 2);
			if (!empty($more_link) && !empty($matches[1])) {
				$more_link = strip_tags(wp_kses_no_null(trim($matches[1])));
			}

			$has_teaser = true;
		} else {
			$content = [$content];
		}

		if ((!$multipage || $page === 1) && strpos($this->post->post_content, '<!--noteaser-->') !== false) {
			$strip_teaser = true;
		}

		$teaser = $content[0];
		if ($more && $strip_teaser && $has_teaser) {
			$teaser = '';
		}

		$output .= $teaser;

		if (count($content) > 1) {
			$id = 'more-' . $this->id();

			if ($more) {
				$output .= Tag::span(['id' => $id]) . $content[1];
			} else {
				if ($more_link) {
					Hook::apply('the_content_more_link', Tag::a([
						'href'  => $this->link() . "#{$id}",
						'class' => 'more-link',
					], $more_link), $more_link);
				}

				$output = force_balance_tags($output);
			}
		}

		if ($preview) {
			$output = preg_replace_callback('/%u([0-9A-F]{4})/', static function ($match) {
				return '&#' . base_convert($match[1], 16, 10) . ';';
			}, $output);
		}

		return $output;
	}

	/**
	 * @param string $content
	 *
	 * @return Document
	 */
	private function getDocument(string $content): Document
	{
		$document = new Document(Site::language());
		$document->loadMarkup(Str::whitespace($content));

		return $document;
	}

	/**
	 * @return Tag
	 */
	private function getPasswordForm(): Tag
	{
		$label = 'password-' . $this->id();

		return Tag::form([
			'method' => 'post',
			'class'  => 'password-form',
			'action' => Site::site_url('wp-login.php?action=postpass', 'login_post'),
		], [
			Tag::p(sprintf(__('This content is password protected. To view it <label for="%s">please enter your password</label> below:', 'twist'), $label)),
			Tag::p(['class' => 'field has-addons'], [
				Tag::div(['class' => 'control'], Tag::input([
					'id'   => $label,
					'name' => 'post_password',
					'type' => 'password',
					'size' => 20,
				])),
				Tag::div(['class' => 'control'], Tag::input([
					'id'    => 'submit',
					'name'  => 'submit',
					'type'  => 'submit',
					'class' => 'button is-primary is-medium',
					'value' => esc_attr_x('Enter', 'post password form'),
				])),
			]),
		]);
	}

	/**
	 * @param string|null $format
	 * @param string      $option
	 *
	 * @return string
	 */
	private function getDatetime(string $format = null, string $option = 'date'): string
	{
		$format = $format ?: (string) get_option("{$option}_format");
		$result = mysql2date($format, $this->post->post_date);

		return Hook::apply("get_the_{$option}", $result, $format, $this->post);
	}

	/**
	 * @param string $date
	 * @param string $filter
	 *
	 * @return string
	 */
	private function getISO8601Datetime(string $date, string $filter): string
	{
		$format = 'c';
		$date   = (string) date($format, strtotime($date));
		$date   = (string) Hook::apply("get_{$filter}", $date, $format, $this->post);

		return Hook::apply($filter, $date, $format, '', '');
	}

	/**
	 * @param bool   $previous
	 * @param string $taxonomy
	 * @param array  $exclude
	 *
	 * @return Post|null
	 */
	private function getAdjacentPost(bool $previous, string $taxonomy = '', array $exclude = []): ?Post
	{
		try {
			$this->setup();

			if ($post = get_adjacent_post(!empty($taxonomy), $exclude, $previous, empty($taxonomy) ? 'category' : $taxonomy)) {
				$post = new static($post);
			}

			$this->reset();
		} catch (AppException $exception) {
			$post = null;
		}

		return $post;
	}

}
