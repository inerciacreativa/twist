<?php

namespace Twist\Model\Image;

use Twist\App\AppException;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Macroable;
use Twist\Library\Support\Url;
use Twist\Model\HasParent;
use Twist\Model\HasParentInterface;
use Twist\Model\ModelInterface;
use Twist\Model\Post\Post;
use WP_Post;
use wpdb;

/**
 * Class Image
 *
 * @package Twist\Model\Image
 */
class Image implements ModelInterface, HasParentInterface
{

	use HasParent;

	use Macroable;

	/**
	 * @var Post
	 */
	protected $image;

	/**
	 * @var ImageMeta
	 */
	protected $meta;

	/**
	 * @var array
	 */
	protected $sizes = [];

	/**
	 * @param string|Url $url
	 *
	 * @return static|null
	 */
	public static function by_path($url): ?self
	{
		/** @var $wpdb wpdb */ global $wpdb;

		if (!($url instanceof Url)) {
			$url = Url::parse($url);
		}

		if (empty($url->path) || $url->path === '/') {
			return null;
		}

		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid RLIKE %s", $url->path);
		$id    = $wpdb->get_var($query);

		if ($id) {
			try {
				return new static($id);
			} catch (AppException $exception) {
				return null;
			}
		}

		return null;
	}

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
			throw new AppException(sprintf('The post (ID %d) is not an attachment (type %s).', $image->id(), $image->type()), false);
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
	 * @inheritDoc
	 */
	public function id(): int
	{
		return $this->image->id();
	}

	/**
	 * @inheritDoc
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

			return Hook::apply('twist_image_image', $image);
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
				'sizes',
				'srcset',
			], array_merge($image, [
				$this->alt(),
				$this->id(),
				$this->sizes($size),
				$this->sources($size),
			]));
		}

		return null;
	}

	/**
	 * @return array|null
	 */
	public function metadata(): ?array
	{
		$meta = $this->meta()->get('_wp_attachment_metadata');
		if (is_array($meta)) {
			return Hook::apply('wp_get_attachment_metadata', $meta, $this->id());
		}

		return null;
	}

	/**
	 * @param string $size
	 *
	 * @return string|null
	 */
	public function sizes(string $size = 'thumbnail'): ?string
	{
		if ($image = wp_get_attachment_image_src($this->id(), $size)) {
			[$source, $width, $height] = $image;

			return $this->getSizes($source, $width, $height);
		}

		return null;
	}

	/**
	 * @param string $size
	 *
	 * @return string|null
	 */
	public function sources(string $size = 'thumbnail'): ?string
	{
		if ($image = wp_get_attachment_image_src($this->id(), $size)) {
			[$source, $width, $height] = $image;

			return $this->getSources($source, $width, $height);
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
		$image     = wp_get_attachment_image_src($this->id(), 'full');
		$file      = get_attached_file($this->id());
		$info      = pathinfo($file);
		$path      = $info['dirname'] . '/' . $info['filename'];
		$extension = '.' . $info['extension'];

		if ($image[1] > $width || $image[2] > $height) {
			$croppedFile = $path . '-' . $width . 'x' . $height . $extension;
			if (file_exists($croppedFile)) {
				$croppedUrl = str_replace(basename($image[0]), basename($croppedFile), $image[0]);

				return $this->resizeInfo($croppedUrl, $width, $height);
			}

			if ($crop === false) {
				$resizedSize = wp_constrain_dimensions($image[1], $image[2], $width, $height);
				$resizedFile = $path . '-' . $resizedSize[0] . 'x' . $resizedSize[1] . $extension;
				if (file_exists($resizedFile)) {
					$resizedUrl = str_replace(basename($image[0]), basename($resizedFile), $image[0]);

					return $this->resizeInfo($resizedUrl, $resizedSize[0], $resizedSize[1]);
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
			$newUrl  = str_replace(basename($image[0]), basename($newFile), $image[0]);

			return $this->resizeInfo($newUrl, $newSize[0], $newSize[1]);
		}

		return $this->resizeInfo($image[0], $image[1], $image[2]);
	}

	/**
	 * @param string $source
	 * @param int    $width
	 * @param int    $height
	 *
	 * @return array
	 */
	protected function resizeInfo(string $source, int $width, int $height): array
	{
		return [
			'src'    => $source,
			'width'  => $width,
			'height' => $height,
			'srcset' => $this->getSources($source, $width, $height),
			'sizes'  => $this->getSizes($source, $width, $height),
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

	/**
	 * @param string $source
	 * @param int    $width
	 * @param int    $height
	 *
	 * @return string
	 */
	protected function getSizes(string $source, int $width, int $height): string
	{
		$meta  = $this->metadata();
		$sizes = wp_calculate_image_sizes([
			$width,
			$height,
		], $source, $meta, $this->id());

		return (string) $sizes;
	}

	/**
	 * @param string $source
	 * @param int    $width
	 * @param int    $height
	 *
	 * @return string
	 */
	protected function getSources(string $source, int $width, int $height): string
	{
		$meta    = $this->metadata();
		$sources = wp_calculate_image_srcset([
			$width,
			$height,
		], $source, $meta, $this->id());

		return (string) $sources;
	}

}
