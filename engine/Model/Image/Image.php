<?php

namespace Twist\Model\Image;

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
	 * @var \WP_Post
	 */
	protected $post;

	/**
	 * @var ImageMeta
	 */
	protected $meta;

	/**
	 * Image constructor.
	 *
	 * @param \WP_Post|int $post
	 * @param Post         $parent
	 */
	public function __construct($post, Post $parent = null)
	{
		if ($post instanceof \WP_Post) {
			$this->post = $post;
		} else if (\is_int($post)) {
			$this->post = get_post($post);
		}

		if (!($this->post instanceof \WP_Post)) {
			throw new \InvalidArgumentException(sprintf('Not valid ID "%s"', $post));
		}

		if ($parent) {
			$this->set_parent($parent);
		}
	}

	/**
	 * @inheritdoc
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
		return $this->parent || $this->post->post_parent > 0;
	}

	/**
	 * @return Post|null
	 */
	public function parent(): ?ModelInterface
	{
		if ($this->parent === null && $this->has_parent()) {
			$this->set_parent(Post::make($this->post->post_parent));
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
		/** @noinspection NullPointerExceptionInspection */
		return $this->has_parent() && ($this->parent()
		                                    ->thumbnail_id() === $this->id());
	}

	/**
	 * @return bool
	 */
	public function set_featured(): bool
	{
		if (!$this->has_parent() || $this->is_featured()) {
			return false;
		}

		/** @noinspection NullPointerExceptionInspection */
		return $this->parent()->meta()->set('_thumbnail_id', $this->id());
	}

	/**
	 * @return ImageMeta
	 */
	public function meta(): ImageMeta
	{
		if ($this->meta === null) {
			$this->meta = new ImageMeta($this);
		}

		return $this->meta;
	}

	/**
	 * @return string
	 */
	public function title(): string
	{
		return $this->post->post_title;
	}

	/**
	 * @return string
	 */
	public function caption(): string
	{
		return wptexturize($this->post->post_excerpt);
	}

	/**
	 * @return string
	 */
	public function alt(): string
	{
		$alt = trim(strip_tags($this->meta()->get('_wp_attachment_image_alt')));
		if (empty($alt) && $this->is_featured()) {
			/** @noinspection NullPointerExceptionInspection */
			$alt = trim(strip_tags($this->parent()->title()));
		}

		return $alt;
	}

	/**
	 * @return string
	 */
	public function link(): string
	{
		return get_attachment_link($this->post);
	}

	/**
	 * @param string $size
	 * @param array  $attributes
	 *
	 * @return Tag|null
	 */
	public function image(string $size = 'thumbnail', array $attributes = []): ?Tag
	{
		if ($image = wp_get_attachment_image($this->id(), $size, false, $attributes)) {
			$tag = Tag::parse($image);

			/** @noinspection NullPointerExceptionInspection */
			return $tag->attributes($attributes);
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
		if ($image = wp_get_attachment_image_src($this->id(), $size)) {
			return array_combine([
				'src',
				'width',
				'height',
				'is_intermediate',
				'alt'
			], array_merge($image, [$this->alt()]));
		}

		return null;
	}

}