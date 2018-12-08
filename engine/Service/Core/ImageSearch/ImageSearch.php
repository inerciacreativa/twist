<?php

namespace Twist\Service\Core\ImageSearch;

use Twist\Model\Post\Post;

/**
 * Class ImageSearch
 *
 * @package Twist\Service\Core\ImageSearch
 */
class ImageSearch
{

	/**
	 * @var array
	 */
	private $modules = [];

	/**
	 * ImageFinder constructor.
	 *
	 * @param ModuleInterface[] $modules
	 */
	public function __construct(array $modules = [])
	{
		foreach ($modules as $module) {
			$this->add($module);
		}
	}

	/**
	 * @param string $module
	 *
	 * @return $this
	 */
	public function add(string $module): self
	{
		if (is_a($module, ModuleInterface::class, true)) {
			$this->modules[] = $module;
		}

		return $this;
	}

	/**
	 * @param Post $post
	 * @param bool $allModules
	 * @param bool $allImages
	 *
	 * @return ImageResolver
	 */
	public function parse(Post $post, bool $allModules = false, bool $allImages = false): ImageResolver
	{
		$resolver = new ImageResolver($post);

		foreach ($this->modules as $module) {
			if ((new $module())->search($resolver, $allImages) && !$allModules) {
				break;
			}
		}

		return $resolver;
	}

}