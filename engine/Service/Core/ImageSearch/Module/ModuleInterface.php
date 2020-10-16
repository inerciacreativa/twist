<?php

namespace Twist\Service\Core\ImageSearch\Module;

use Twist\Service\Core\ImageSearch\ImageResolver;

/**
 * Interface ImageSearchInterface
 *
 * @package Twist\Service\Core\ImageSearch
 */
interface ModuleInterface
{

	/**
	 * @param ImageResolver $resolver
	 * @param bool          $all
	 *
	 * @return bool
	 */
	public function search(ImageResolver $resolver, bool $all = false): bool;

}
