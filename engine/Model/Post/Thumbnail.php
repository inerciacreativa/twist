<?php

namespace Twist\Model\Post;

use Twist\Library\Util\Tag;

/**
 * Class Thumbnail
 *
 * @package Twist\Model\Post
 */
class Thumbnail
{

	/**
	 * @var Post
	 */
	protected $post;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $size = 'post-thumbnail';

	/**
	 * @var Tag
	 */
	protected $image;

	/**
	 * @var int
	 */
	protected $width;

	/**
	 * @var int
	 */
	protected $height;

	/**
	 * Thumbnail constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		$this->post = $post;
		$this->id = (int) $post->metas()['_thumbnail_id'];
	}

	/**
	 * @return bool
	 */
	public function exists(): bool
	{
		return (bool) $this->id;
	}

	/**
	 * @param string $size
	 */
	public function size(string $size)
	{
		$size = (string) apply_filters('post_thumbnail_size', $size);

		if (!empty($size) && $size !== $this->size) {
			$this->image = null;
			$this->size = $size;
		}
	}

	/**
	 * @param array $attributes
	 *
	 * @return Tag|null
	 */
	public function image(array $attributes = [])
	{
		if ($this->id === 0) {
			return null;
		}

		if (array_key_exists('size', $attributes)) {
			$this->size($attributes['size']);
			unset($attributes['size']);
		}

		if ($this->image === null) {
			$image = wp_get_attachment_image($this->id, $this->size);
			$image = apply_filters('post_thumbnail_html', $image, $this->post->id(), $this->id, $this->size, []);

			$this->image = Tag::parse($image);
		}

		$image = $this->image->attributes($attributes);

		if (!isset($image['alt'])) {
			$image['alt'] = '';
		}

		return $image;
	}

	/**
	 * @return string
	 */
	public function source(): string
	{
		return $this->get('src');
	}

	/**
	 * @return int
	 */
	public function width(): int
	{
		return (int) $this->get('width');
	}

	/**
	 * @return int
	 */
	public function height(): int
	{
		return (int) $this->get('height');
	}

	/**
	 * @param string $attribute
	 *
	 * @return null|string
	 */
	protected function get(string $attribute)
	{
		if ($this->id === 0) {
			return null;
		}

		$image = $this->image();

		return $image instanceof Tag ? $image[$attribute] : null;
	}

}