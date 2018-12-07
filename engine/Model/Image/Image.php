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
	 * @var ImageMeta
	 */
	protected $meta;

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
			if ($parent->id() === $post->parent_id()) {
				$this->set_parent($parent);
				$post->set_parent($parent);
			} else {
				new AppException(sprintf('The parent passed (ID %d) is not the same as the post (ID %d) parent (ID %d)', $parent->id(), $this->post->id(), $this->post->parent_id()));
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
	 * @throws AppException
	 */
	public function is_featured(): bool
	{
		/** @noinspection NullPointerExceptionInspection */
		return $this->has_parent() && ($this->parent()
		                                    ->thumbnail_id() === $this->id());
	}

	/**
	 * @return bool
	 * @throws AppException
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
	 * @throws AppException
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
	 * @throws AppException
	 */
	public function get(string $size = 'thumbnail'): ?array
	{
		if ($image = wp_get_attachment_image_src($this->id(), $size)) {
			return array_combine([
				'src',
				'width',
				'height',
				'is_intermediate',
				'alt',
			], array_merge($image, [$this->alt()]));
		}

		return null;
	}

}