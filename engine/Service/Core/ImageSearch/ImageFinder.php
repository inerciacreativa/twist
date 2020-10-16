<?php

namespace Twist\Service\Core\ImageSearch;

use Twist\Model\Image\Image;
use Twist\Service\Core\ImageSearch\Module\AttachmentModule;
use Twist\Service\Core\ImageSearch\Module\ContentModule;
use Twist\Service\Core\ImageSearch\Module\ModuleInterface;
use Twist\Service\Core\ImageSearch\Module\TedModule;
use Twist\Service\Core\ImageSearch\Module\VimeoModule;
use Twist\Service\Core\ImageSearch\Module\YouTubeModule;

/**
 * Class ImageSearch
 *
 * @package Twist\Service\Core\ImageSearch
 */
class ImageFinder
{

	protected static $classes = [
		ContentModule::class,
		YouTubeModule::class,
		VimeoModule::class,
		TedModule::class,
		AttachmentModule::class,
	];

	/**
	 * @var array
	 */
	protected $modules = [];

	/**
	 * ImageFinder constructor.
	 *
	 * @param ModuleInterface[] $modules
	 */
	public function __construct(array $modules = [])
	{
		foreach (self::$classes as $module) {
			$this->add($module);
		}

		foreach ($modules as $module) {
			$this->add($module, true);
		}
	}

	/**
	 * @param string $module
	 * @param bool   $check
	 *
	 * @return $this
	 */
	public function add(string $module, bool $check = false): self
	{
		if (!$check || is_a($module, ModuleInterface::class, true)) {
			$this->modules[] = new $module();
		}

		return $this;
	}

	/**
	 * @param ImageResolver $resolver
	 * @param bool          $allModules
	 * @param bool          $allImages
	 *
	 * @return Image|null
	 */
	public function search(ImageResolver $resolver, bool $allModules = false, bool $allImages = false): ?Image
	{
		foreach ($this->modules as $module) {
			if (!$allModules && $module->search($resolver, $allImages)) {
				break;
			}
		}

		return $resolver->get();
	}

}
