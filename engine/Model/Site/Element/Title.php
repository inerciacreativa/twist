<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;
use Twist\Library\Util\Tag;

/**
 * Class Title
 *
 * @package Twist\Model\Site\Element
 */
class Title implements ElementInterface
{

	/**
	 * @var array
	 */
	protected $title = [];

	/**
	 * @inheritdoc
	 */
	public function parse(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('title');

		while ($node = $nodes->item(0)) {
			$this->title[] = Tag::title($node->nodeValue);

			$node->parentNode->removeChild($node);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function get(): array
	{
		return $this->title;
	}

}