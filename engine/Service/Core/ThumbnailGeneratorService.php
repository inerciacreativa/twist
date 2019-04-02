<?php

namespace Twist\Service\Core;

use Twist\Model\Post\Meta;
use Twist\Model\Post\Post;
use Twist\Model\Post\Query;
use Twist\Service\Core\ImageSearch\ImageFinder;
use Twist\Service\Core\ImageSearch\ImageResolver;
use Twist\Service\Service;

/**
 * Class ThumbnailGeneratorService
 *
 * @package Twist\Service\Core
 */
class ThumbnailGeneratorService extends Service
{

	/**
	 * @var ImageFinder
	 */
	protected $finder;

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
	 */
	protected function check($value, string $key, $meta)
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
	 */
	protected function search(Post $post): int
	{
		$resolver = new ImageResolver($post);

		if (($image = $this->getFinder()->search($resolver)) && $image->set_featured()) {
			return $image->id();
		}

		return 0;
	}

	/**
	 * @return ImageFinder
	 */
	protected function getFinder(): ImageFinder
	{
		if ($this->finder === null) {
			$this->finder = new ImageFinder($this->config('modules', []));
		}

		return $this->finder;
	}

}