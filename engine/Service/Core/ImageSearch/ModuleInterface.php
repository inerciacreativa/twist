<?php

namespace Twist\Service\Core\ImageSearch;

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