<?php

namespace Twist\Service\Core\ImageSearch;

use Twist\Model\Post\Post;

/**
 * Class ExternalImage
 *
 * @package Twist\Service\Core\ImageSearch
 */
class ExternalImage
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
	 * @param Post $post
	 *
	 * @return bool|int
	 */
	public function download(Post $post)
	{
		if (!\function_exists('download_url')) {
			include ABSPATH . 'wp-admin/includes/file.php';
		}

		if (!preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $this->image['src'], $matches)) {
			return false;
		}

		$temp = [
			'name'     => basename($matches[0]),
			'tmp_name' => download_url($this->image['src']),
		];

		if (is_wp_error($temp['tmp_name'])) {
			return false;
		}

		if (!\function_exists('media_handle_sideload')) {
			include ABSPATH . 'wp-admin/includes/media.php';
		}

		if (!\function_exists('wp_read_image_metadata')) {
			include ABSPATH . 'wp-admin/includes/image.php';
		}

		$id = media_handle_sideload($temp, $post->id(), $this->image['alt']);

		if (is_wp_error($id)) {
			@unlink($temp['tmp_name']);

			return false;
		}

		return $id;
	}

}