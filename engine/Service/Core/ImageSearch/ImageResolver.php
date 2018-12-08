<?php

namespace Twist\Service\Core\ImageSearch;

use Twist\App\AppException;
use Twist\Library\Util\Url;
use Twist\Model\Image\Image;
use Twist\Model\Post\Post;

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
	private $post;

	/**
	 * @var array
	 */
	private $images = [];

	/**
	 * @var bool
	 */
	private $sorted = true;

	/**
	 * ImageResolver constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		$this->post = $post;
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
		return $this->post->content(null, true, true);
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return \count($this->images);
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
		} else if (\is_array($image)) {
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
	 * @return array
	 */
	public function all(): array
	{
		$this->sort();

		return $this->images;
	}

	/**
	 * @param bool $once
	 *
	 * @return null|Image
	 */
	public function get(bool $once = false): ?Image
	{
		if (empty($this->images)) {
			return null;
		}

		$this->sort();

		for ($i = 0, $count = $this->count(); $i < $count; $i++) {
			if ($image = $this->image($this->images[$i])) {
				return $this->images[$i] = $image;
			}
			if ($once) {
				break;
			}
		}

		return null;
	}

	/**
	 * @param array|Image $image
	 *
	 * @return Image|null
	 */
	public function image($image): ?Image
	{
		if ($image instanceof Image) {
			return $image;
		}

		$result = null;
		if (isset($image['id']) && $image['id'] > 0) {
			$id = $image['id'];
		} else {
			$home   = Url::parse(home_url());
			$source = Url::parse($image['src']);

			if ($source->host === $home->host) {
				$source->scheme = $home->scheme;

				$id = $this->getLocalImage($source);
			} else {
				$id = $this->getExternalImage($image);
			}
		}

		if ($id) {
			try {
				$result = new Image($id, $this->post);
			} catch (AppException $exception) {
				$result = null;
			}
		}

		return $result;
	}

	/**
	 * @param Url $source
	 *
	 * @return int
	 */
	protected function getLocalImage(Url $source): int
	{
		global $wpdb;

		$base = wp_upload_dir()['baseurl'] . '/';
		if (strpos($source, $base) === false) {
			return 0;
		}

		$slug = str_replace($base, '', $source);
		$slug = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $slug);

		/** @noinspection SqlResolve */
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
	protected function getExternalImage(array $image): int
	{
		if (!\function_exists('download_url')) {
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

		if (!\function_exists('media_handle_sideload')) {
			include ABSPATH . 'wp-admin/includes/media.php';
		}

		if (!\function_exists('wp_read_image_metadata')) {
			include ABSPATH . 'wp-admin/includes/image.php';
		}

		$id = media_handle_sideload($temp, $this->post->id(), $image['alt']);

		if (is_wp_error($id)) {
			@unlink($temp['tmp_name']);

			return 0;
		}

		return (int) $id;
	}

	/**
	 * @return $this
	 */
	protected function sort(): self
	{
		if ($this->sorted || $this->count() < 2) {
			return $this;
		}

		usort($this->images, function ($a, $b) {
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

}