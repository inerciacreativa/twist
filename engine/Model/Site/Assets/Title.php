<?php

namespace Twist\Model\Site\Assets;

use Twist\Library\Dom\Document;
use Twist\Library\Html\Tag;

/**
 * Class Title
 *
 * @package Twist\Model\Site\Element
 */
class Title implements AssetsInterface
{

	/**
	 * @var array
	 */
	private $title = [];

	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function all(): array
	{
		return $this->title;
	}

}
