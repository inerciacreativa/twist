<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;

/**
 * Interface ElementInterface
 *
 * @package Twist\Model\Site\Element
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