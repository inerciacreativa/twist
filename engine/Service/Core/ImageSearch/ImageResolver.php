<?php

namespace Twist\Service\Core\ImageSearch;

use Twist\App\AppException;
use Twist\Library\Dom\Document;
use Twist\Library\Support\Url;
use Twist\Model\Image\Image;
use Twist\Model\Post\Post;
use Twist\Twist;

/**
 * Class ImageResolver
 *
 * @package Twist\Service\Core\ImageSearch
 */
class ImageResolver
{

	/**
	 * @var Post
	 */
	protected $post;

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var Document
	 */
	protected $document;

	/**
	 * @var array
	 */
	protected $images = [];

	/**
	 * @var bool
	 */
	protected $sorted = false;

	/**
	 * ImageResolver constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		$this->post     = $post;
		$this->content  = $post->content(['filter' => false]);
		$this->document = $post->document();
	}

	/**
	 * @return Post
	 */
	public function post(): Post
	{
		return $this->post;
	}

	/**
	 * @return string
	 */
	public function content(): string
	{
		return $this->content;
	}

	/**
	 * @return Document
	 */
	public function document(): Document
	{
		return $this->document;
	}

	/**
	 * @param $image
	 *
	 * @return $this
	 */
	public function add($image): self
	{
		if ($image instanceof Image) {
			$this->sorted   = false;
			$this->images[] = $image;
		} else if (is_array($image)) {
			$this->sorted   = false;
			$this->images[] = array_merge([
				'id'     => 0,
				'src'    => '',
				'alt'    => '',
				'width'  => 0,
				'height' => 0,
			], $image);
		}

		return $this;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->images);
	}

	/**
	 * @return array
	 */
	public function all(): array
	{
		$this->sort();

		return $this->images;
	}

	/**
	 * @return null|Image
	 */
	public function get(): ?Image
	{
		$this->sort();

		foreach ($this->images as $index => $image) {
			if ($object = $this->getObject($image)) {
				return $this->images[$index] = $object;
			}
		}

		return null;
	}

	/**
	 * @return $this
	 */
	protected function sort(): self
	{
		if ($this->sorted || $this->count() < 2) {
			$this->sorted = true;

			return $this;
		}

		usort($this->images, static function ($a, $b) {
			$ai = $a instanceof Image ? $a->get('large') : $a;
			$bi = $b instanceof Image ? $b->get('large') : $b;

			$ad = ($ai['width'] * 10) + $ai['height'];
			$bd = ($bi['width'] * 10) + $bi['height'];

			if ($ad === $bd) {
				return 0;
			}

			return ($ad > $bd) ? -1 : 1;
		});

		$this->sorted = true;

		return $this;
	}

	/**
	 * @param array|Image $image
	 *
	 * @return Image|null
	 */
	protected function getObject($image): ?Image
	{
		if ($image instanceof Image) {
			return $image;
		}

		if ($object = $this->getImage($image)) {
			return $object;
		}

		$source = Url::parse($image['src']);

		if ($source->isLocal()) {
			$id = $this->getLocal($source);
		} else {
			$id = $this->getExternal($image);
		}

		if ($id) {
			try {
				$object = new Image($id, $this->post);
			} catch (AppException $exception) {
				$object = null;
			}
		} else {
			$object = null;
		}

		return $object;
	}

	/**
	 * @param array $image
	 *
	 * @return Image|null
	 */
	protected function getImage(array $image): ?Image
	{
		$object = null;

		if (isset($image['id']) && $image['id'] > 0 && Post::exists_id($image['id'])) {
			try {
				$object = new Image($image['id'], $this->post);
			} catch (AppException $exception) {
				$object = null;
			}
		}

		return $object;
	}

	/**
	 * @param Url $source
	 *
	 * @return int
	 */
	protected function getLocal(Url $source): int
	{
		global $wpdb;

		$base = wp_upload_dir()['baseurl'] . '/';
		if (strpos($source, $base) === false) {
			return 0;
		}

		$source->query    = [];
		$source->fragment = '';

		$slug = str_replace($base, '', $source);
		$slug = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $slug);

		$result = $wpdb->get_row($wpdb->prepare("SELECT posts.ID FROM $wpdb->posts AS posts, $wpdb->postmeta AS meta WHERE posts.ID = meta.post_id AND meta.meta_key = '_wp_attached_file' AND meta.meta_value = '%s' AND posts.post_type = 'attachment' LIMIT 1", $slug));

		if (!$result) {
			return 0;
		}

		return (int) $result->ID;
	}

	/**
	 * @param array $image
	 *
	 * @return int
	 */
	protected function getExternal(array $image): int
	{
		if (!function_exists('download_url')) {
			include ABSPATH . 'wp-admin/includes/file.php';
		}

		if (!preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image['src'], $matches)) {
			return 0;
		}

		$temp = [
			'name'     => basename($matches[0]),
			'tmp_name' => download_url($image['src']),
		];

		if (is_wp_error($temp['tmp_name'])) {
			return 0;
		}

		if (!function_exists('media_handle_sideload')) {
			include ABSPATH . 'wp-admin/includes/media.php';
		}

		if (!function_exists('wp_read_image_metadata')) {
			include ABSPATH . 'wp-admin/includes/image.php';
		}

		$id = media_handle_sideload($temp, $this->post->id(), $image['alt']);

		if (is_wp_error($id)) {
			@unlink($temp['tmp_name']);

			return 0;
		}

		return (int) $id;
	}

}
