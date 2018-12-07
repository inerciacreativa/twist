<?php

namespace Twist\Service\Core\ImageSearch;

use Twist\App\AppException;
use Twist\Library\Util\Url;
use Twist\Model\Image\Image;
use Twist\Model\Post\Post;

/**
 * Class ExternalImage
 *
 * @package Twist\Service\Core\ImageSearch
 */
class ImageFinder
{

	/**
	 * @var array
	 */
	protected $image;


	/**
	 * ExternalImage constructor.
	 *
	 * @param array $image
	 */
	public function __construct(array $image)
	{
		$this->image = $image;
	}

	/**
	 * @param Post $parent
	 *
	 * @return null|Image
	 * @throws AppException
	 */
	public function get(Post $parent): ?Image
	{
		if (isset($this->image['id']) && $this->image['id'] > 0) {
			$id = $this->image['id'];
		} else {
			$home   = Url::parse(home_url());
			$source = Url::parse($this->image['src']);

			if ($source->host === $home->host) {
				$source->scheme = $home->scheme;

				$id = $this->local($source);
			} else {
				$id = $this->external($this->image, $parent);
			}
		}

		if ($id) {
			return new Image($id, $parent);
		}

		return null;
	}

	/**
	 * @param Url $source
	 *
	 * @return int
	 */
	protected function local(Url $source): int
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
	 * @param Post  $post
	 *
	 * @return int
	 */
	protected function external(array $image, Post $post): int
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

		$id = media_handle_sideload($temp, $post->id(), $image['alt']);

		if (is_wp_error($id)) {
			@unlink($temp['tmp_name']);

			return 0;
		}

		return (int) $id;
	}

}