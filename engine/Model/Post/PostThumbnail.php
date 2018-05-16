<?php

namespace Twist\Model\Post;

use Twist\Library\Image\ImageSearch;
use Twist\Library\Util\Arr;
use Twist\Library\Util\Tag;

/**
 * Class PostThumbnail
 *
 * @package Twist\Model\Post
 */
class PostThumbnail
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
	 * PostThumbnail constructor.
	 *
	 * @param Post $post
	 * @param bool $generate
	 */
	public function __construct(Post $post, bool $generate = false)
	{
		$this->post = $post;
		$this->id   = (int) $post->meta()->get('_thumbnail_id');
		if (!$this->id && $generate) {
			$this->id = $this->generate();
		}
	}

	/**
	 * @return bool
	 */
	public function exists(): bool
	{
		return (bool) $this->id;
	}

	/**
	 * @param array $attributes
	 *
	 * @return Tag|null
	 */
	public function image(array $attributes = []): ?Tag
	{
		if ($this->id === 0) {
			return null;
		}

		$size = Arr::pull($attributes, 'size', $this->size);
		$size = (string) apply_filters('post_thumbnail_size', $size);

		$image = wp_get_attachment_image($this->id, $size, false, $attributes);
		$image = apply_filters('post_thumbnail_html', $image, $this->post->id(), $this->id, $size, []);

		if ($tag = Tag::parse($image)) {
			$tag->attributes($attributes);

			return $this->image = $tag;
		}

		return null;
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
	protected function get(string $attribute): ?string
	{
		if ($this->id === 0 || $this->image === null) {
			return null;
		}

		return $this->image[$attribute];
	}

	/**
	 * @return int
	 */
	protected function generate(): int
	{
		$content = $this->post->raw_content();
		$images  = ImageSearch::find($content, true);

		if ($images->count()) {
			return (int) $images->sortBySize()
			                    ->first()
			                    ->setFeatured($this->post->id());
		}

		return 0;
	}

}