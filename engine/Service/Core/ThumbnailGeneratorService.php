<?php

namespace Twist\Service\Core;

use Twist\App\AppException;
use Twist\Model\Post\Meta;
use Twist\Model\Post\Post;
use Twist\Model\Post\Query;
use Twist\Service\Core\ImageSearch\ImageSearch;
use Twist\Service\Core\ImageSearch\ImageSearchInterface;
use Twist\Service\Core\ImageSearch\TedSearch;
use Twist\Service\Core\ImageSearch\VimeoSearch;
use Twist\Service\Core\ImageSearch\YouTubeSearch;
use Twist\Service\Service;

/**
 * Class ThumbnailGeneratorService
 *
 * @package Twist\Service\Core
 */
class ThumbnailGeneratorService extends Service
{

	protected static $video = [
		YouTubeSearch::class,
		VimeoSearch::class,
		TedSearch::class,
	];

	/**
	 * @inheritdoc
	 */
	public function boot(): bool
	{
		return $this->config('enable') && !Query::is_admin();
	}

	/**
	 * @inheritdoc
	 */
	protected function init(): void
	{
		$this->hook()->on('twist_meta_post', 'check', ['arguments' => 3]);
	}

	/**
	 * @param mixed  $value
	 * @param string $key
	 * @param Meta   $meta
	 *
	 * @return mixed
	 * @throws AppException
	 */
	protected function check($value, string $key, Meta $meta)
	{
		$this->hook()->disable();

		if ($key === '_thumbnail_id' && empty($value) && ($id = $this->search($meta->parent()))) {
			$value = $id;
		}

		$this->hook()->enable();

		return $value;
	}

	/**
	 * @param Post $post
	 *
	 * @return int
	 * @throws AppException
	 */
	protected function search(Post $post): int
	{
		$classes = [ImageSearch::class];

		if ($this->config('videos')) {
			$classes = array_merge($classes, self::$video);
		}

		foreach ($this->config('extra', []) as $class) {
			if (is_a($class, ImageSearchInterface::class, true)) {
				$classes[] = $class;
			}
		}

		foreach ($classes as $class) {
			/** @var ImageSearchInterface $search */
			$search = new $class;

			/** @noinspection NotOptimalIfConditionsInspection */
			if ($search->search($post->raw_content()) && ($image = $search->get()) && ($image = $image->get($post)) && $image->set_featured()) {
				return $image->id();
			}
		}

		if ($post->images()->count() > 0 && ($image = $post->images()
		                                                   ->sort()
		                                                   ->first()) && $image->set_featured()) {
			return $image->id();
		}

		return 0;
	}

}