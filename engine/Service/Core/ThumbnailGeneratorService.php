<?php

namespace Twist\Service\Core;

use Twist\Model\Post\Meta;
use Twist\Model\Post\Post;
use Twist\Model\Post\Query;
use Twist\Service\Core\ImageSearch\AttachmentModule;
use Twist\Service\Core\ImageSearch\ContentModule;
use Twist\Service\Core\ImageSearch\ImageSearch;
use Twist\Service\Core\ImageSearch\TedModule;
use Twist\Service\Core\ImageSearch\VimeoModule;
use Twist\Service\Core\ImageSearch\YouTubeModule;
use Twist\Service\Service;

/**
 * Class ThumbnailGeneratorService
 *
 * @package Twist\Service\Core
 */
class ThumbnailGeneratorService extends Service
{

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
		$modules = array_merge([
			ContentModule::class,
		], $this->config('modules', []), [
			YouTubeModule::class,
			VimeoModule::class,
			TedModule::class,
			AttachmentModule::class,
		]);

		$search = new ImageSearch($modules);
		if (($image = $search->parse($post)->get()) && $image->set_featured()) {
			return $image->id();
		}

		return 0;
	}

}