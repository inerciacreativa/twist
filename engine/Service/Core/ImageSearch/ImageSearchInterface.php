<?php

namespace Twist\Service\Core\ImageSearch;

/**
 * Interface ImageSearchInterface
 *
 * @package Twist\Service\Core\ImageSearch
 */
interface ImageSearchInterface
{

	/**
	 * @param string $html
	 * @param int    $width
	 *
	 * @return bool
	 */
	public function search(string $html, int $width = 720): bool;

	/**
	 * @return null|ExternalImage
	 */
	public function get(): ?ExternalImage;

}