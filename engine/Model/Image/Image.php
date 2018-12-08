<?php

namespace Twist\Model\Image;

use Twist\App\AppException;
use Twist\Library\Hook\Hook;
use Twist\Library\Model\Model;
use Twist\Library\Model\ModelInterface;
use Twist\Library\Util\Tag;
use Twist\Model\Post\Post;

/**
 * Class Image
 *
 * @package Twist\Model\Image
 */
class Image extends Model
{

	/**
	 * @var Post
	 */
	protected $post;

	/**
	 * @var Meta
	 */
	protected $meta;

	/**
	 * @var array
	 */
	protected $sizes = [];

	/**
	 * Image constructor.
	 *
	 * @param Post|\WP_Post|int $post
	 * @param Post              $parent
	 *
	 * @throws AppException
	 */
	public function __construct($post, Post $parent = null)
	{
		if (!($post instanceof Post)) {
			$post = Post::make($post);
		}

		if ($post->type() !== 'attachment') {
			new AppException(sprintf('The post (ID %d) is not an attachment (type %s).', $this->id(), $this->post->type()));
		}

		if ($parent) {
			$this->set_parent($parent);

			if ($parent->id() === $post->parent_id()) {
				$post->set_parent($parent);
			}
		}

		$this->post = $post;
	}

	/**
	 * @inheritdoc
	 */
	public function id(): int
	{
		return $this->post->id();
	}

	/**
	 * @inheritdoc
	 */
	public function has_parent(): bool
	{
		return $this->parent || $this->post->has_parent();
	}

	/**
	 * @return Post|null
	 * @throws AppException
	 */
	public function parent(): ?ModelInterface
	{
		if ($this->parent === null && $this->has_parent()) {
			$this->set_parent($this->post->parent());
		}

		return $this->parent;
	}

	/**
	 * @inheritdoc
	 */
	public function has_children(): bool
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function is_featured(): bool
	{
		try {
			/** @noinspection NullPointerExceptionInspection */
			return $this->has_parent() && ($this->parent()
			                                    ->thumbnail_id() === $this->id());
		} catch (AppException $exception) {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function set_featured(): bool
	{
		if (!$this->has_parent() || $this->is_featured()) {
			return false;
		}

		try {
			/** @noinspection NullPointerExceptionInspection */
			return $this->parent()->meta()->set('_thumbnail_id', $this->id());
		} catch (AppException $exception) {
			return false;
		}
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
	 * @return string
	 */
	public function title(): string
	{
		return $this->post->title();
	}

	/**
	 * @return string
	 */
	public function caption(): string
	{
		return wptexturize($this->post->excerpt());
	}

	/**
	 * @return string
	 */
	public function alt(): string
	{
		$alt = trim(strip_tags($this->meta()->get('_wp_attachment_image_alt')));
		if (empty($alt) && $this->is_featured()) {
			try {
				/** @noinspection NullPointerExceptionInspection */
				$alt = trim(strip_tags($this->parent()->title()));
			} catch (AppException $exception) {
				$alt = '';
			}
		}

		return $alt;
	}

	/**
	 * @return string
	 */
	public function link(): string
	{
		return get_attachment_link($this->post->object());
	}

	/**
	 * @param string $size
	 * @param array  $attributes
	 *
	 * @return Tag|null
	 */
	public function image(string $size = 'thumbnail', array $attributes = []): ?Tag
	{
		if (($image = wp_get_attachment_image($this->id(), $size, false, $attributes)) && ($tag = Tag::parse($image))) {
			$image = $tag->attributes($attributes);

			return Hook::apply('twist_post_image', $image);
		}

		return null;
	}

	/**
	 * @param string $size
	 *
	 * @return array|null
	 */
	public function get(string $size = 'thumbnail'): ?array
	{
		if (array_key_exists($size, $this->sizes)) {
			return $this->sizes[$size];
		}

		if ($image = wp_get_attachment_image_src($this->id(), $size)) {
			return $this->sizes[$size] = array_combine([
				'src',
				'width',
				'height',
				'is_intermediate',
				'alt',
				'id',
			], array_merge($image, [$this->alt(), $this->id()]));
		}

		return null;
	}

}