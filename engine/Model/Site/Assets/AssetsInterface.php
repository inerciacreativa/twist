<?php

namespace Twist\Model\Site\Assets;

use Twist\Library\Dom\Document;
use Twist\Library\Html\Tag;

/**
 * Interface AssetsInterface
 *
 * @package Twist\Model\Site\Assets
 */
interface AssetsInterface
{

	/**
	 * Walk the DOM extracting and removing the appropriate assets.
	 *
	 * @param Document $dom
	 */
	public function get(Document $dom): void;

	/**
	 * Return the extracted assets as Tag objects.
	 *
	 * @return Tag[]
	 */
	public function all(): array;

}
