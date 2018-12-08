<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;
use Twist\Library\Util\Tag;

/**
 * Class Title
 *
 * @package Twist\Model\Site\Element
 */
class Title implements ElementsInterface
{

	/**
	 * @var array
	 */
	private $title = [];

	/**
	 * @inheritdoc
	 */
	public function get(Document $dom): void
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
	public function all(): array
	{
		return $this->title;
	}

}