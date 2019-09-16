<?php

namespace Twist\Model\Image;

use Twist\App\AppException;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Macroable;
use Twist\Model\Base\Model;
use Twist\Model\Base\ModelInterface;
use Twist\Model\Post\Post;
use WP_Post;

/**
 * Class Image
 *
 * @package Twist\Model\Image
 */
class Image extends Model
{

	use Macroable;

	/**
	 * @var Post
	 */
	protected $image;

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
	 * @param Post|WP_Post|int $image
	 * @param Post             $parent
	 *
	 * @throws AppException
	 */
	public function __construct($image, Post $parent = null)
	{
		if (!($image instanceof Post)) {
			$image = Post::make($image);
		}

		if ($image->type() !== 'attachment') {
			new AppException(sprintf('The post (ID %d) is not an attachment (type %s).', $this->id(), $this->image->type()));
		}

		if ($parent) {
			$this->set_parent($parent);

			if ($parent->id() === $image->parent_id()) {
				$image->set_parent($parent);
			}
		}

		$this->image = $image;
	}

	/**
	 * @inheritdoc
	 */
	public function id(): int
	{
		return $this->image->id();
	}

	/**
	 * @inheritdoc
	 */
	public function has_parent(): bool
	{
		return $this->parent || $this->image->has_parent();
	}

	/**
	 * @return Post|null
	 */
	public function parent(): ?ModelInterface
	{
		if ($this->parent === null && $this->has_parent()) {
			try {
				$this->set_parent($this->image->parent());
			} catch (AppException $exception) {
				return null;
			}
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
		/** @noinspection NullPointerExceptionInspection */
		if (!$this->has_parent() || $this->is_featured() || $this->parent()
		                                                         ->is_preview()) {
			return false;
		}

		/** @noinspection NullPointerExceptionInspection */
		$this->parent()->meta()->set('_thumbnail_id', $this->id());

		return true;
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
		return $this->image->title();
	}

	/**
	 * @return string
	 */
	public function caption(): string
	{
		return wptexturize($this->image->excerpt());
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
		return get_attachment_link($this->image->object());
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

	/**
	 * @param int  $width
	 * @param int  $height
	 * @param bool $crop
	 * @param int  $quality
	 *
	 * @return array|null
	 */
	public function resize(int $width, int $height, bool $crop, int $quality = 90): ?array
	{
		$source    = wp_get_attachment_image_src($this->id(), 'full');
		$file      = get_attached_file($this->id());
		$info      = pathinfo($file);
		$path      = $info['dirname'] . '/' . $info['filename'];
		$extension = '.' . $info['extension'];

		if ($source[1] > $width || $source[2] > $height) {
			$croppedFile = $path . '-' . $width . 'x' . $height . $extension;
			if (file_exists($croppedFile)) {
				$croppedUrl = str_replace(basename($source[0]), basename($croppedFile), $source[0]);

				return [
					'url'    => $croppedUrl,
					'width'  => $width,
					'height' => $height,
				];
			}

			if ($crop === false) {
				$resizedSize = wp_constrain_dimensions($source[1], $source[2], $width, $height);
				$resizedFile = $path . '-' . $resizedSize[0] . 'x' . $resizedSize[1] . $extension;
				if (file_exists($resizedFile)) {
					$resizedUrl = str_replace(basename($source[0]), basename($resizedFile), $source[0]);

					return [
						'url'    => $resizedUrl,
						'width'  => $resizedSize[0],
						'height' => $resizedSize[1],
					];
				}
			}

			$size = @getimagesize($file);
			if ($size[0] < $width) {
				$width = (int) $size[0] - 1;
			}

			$newFile = $this->resizeHelper($file, $width, $height, $crop, $quality);
			if ($newFile === null) {
				return null;
			}

			$newSize = getimagesize($newFile);
			$newUrl  = str_replace(basename($source[0]), basename($newFile), $source[0]);

			return [
				'url'    => $newUrl,
				'width'  => $newSize[0],
				'height' => $newSize[1],
			];
		}

		return [
			'url'    => $source[0],
			'width'  => $source[1],
			'height' => $source[2],
		];
	}

	/**
	 * @param string $file
	 * @param int    $width
	 * @param int    $height
	 * @param bool   $crop
	 * @param int    $quality
	 *
	 * @return string|null
	 */
	protected function resizeHelper(string $file, int $width, int $height, bool $crop, int $quality): ?string
	{
		$editor = wp_get_image_editor($file);
		if (is_wp_error($editor)) {
			return null;
		}

		$editor->set_quality($quality);
		if (is_wp_error($editor->resize($width, $height, $crop))) {
			return null;
		}

		$resized = $editor->generate_filename();
		if (is_wp_error($editor->save())) {
			return null;
		}

		return $resized;
	}

}
