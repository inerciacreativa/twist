<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;

/**
 * Interface ElementInterface
 *
 * @package Twist\Model\Site\Element
 */
interface ElementInterface
{

	/**
	 * @param Document $dom
	 */
	public function parse(Document $dom): void;

	/**
	 * @return array
	 */
	public function get(): array;

}