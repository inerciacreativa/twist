<?php

namespace Twist\Model\Site\Elements;

use Twist\Library\Dom\Document;

/**
 * Interface ElementInterface
 *
 * @package Twist\Model\Site\Elements
 */
interface ElementsInterface
{

	/**
	 * @param Document $dom
	 */
	public function get(Document $dom): void;

	/**
	 * @return array
	 */
	public function all(): array;

}