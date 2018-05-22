<?php

namespace Twist\Service\Core;

use Twist\Model\Post\Post;
use Twist\Model\Post\PostMeta;
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
	public function boot(): void
	{
		$this->hook()->off('twist_meta_post', 'check', ['arguments' => 3]);

		if ($this->config->get('service.thumbnail_generator.enable')) {
			$this->start();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function start(): void
	{
		$this->hook()->enable();
	}

	/**
	 * @inheritdoc
	 */
	public function stop(): void
	{
		$this->hook()->disable();
	}

	/**
	 * @param mixed    $value
	 * @param string   $key
	 * @param PostMeta $meta
	 *
	 * @return mixed
	 */
	protected function check($value, string $key, $meta)
	{
		$this->stop();
		if ($key === '_thumbnail_id' && empty($value) && ($id = $this->search($meta->parent()))) {
			$value = $id;
		}

		$this->start();

		return $value;
	}

	/**
	 * @param Post $post
	 *
	 * @return bool|int
	 */
	protected function search(Post $post)
	{
		$classes = [ImageSearch::class];

		if ($this->config->get('service.thumbnail_generator.videos')) {
			$classes = array_merge($classes, self::$video);
		}

		foreach ((array) $this->config->get('service.thumbnail_generator.extra', []) as $class) {
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

		if ($post->images()->count() > 0 && ($image = $post->images()->sort()->first()) && $image->set_featured()) {
			return $image->id();
		}

		return false;
	}

}