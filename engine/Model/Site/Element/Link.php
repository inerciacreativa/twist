<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;
use Twist\Library\Util\Tag;

/**
 * Class Link
 *
 * @package Twist\Model\Site\Element
 */
class Link implements ElementInterface
{

	/**
	 * @var array
	 */
	protected $links = [];

	/**
	 * @var array
	 */
	protected $styles = [];

	/**
	 * @inheritdoc
	 */
	public function parse(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('link');

		while ($node = $nodes->item(0)) {
			$attributes = [];

			if ($node->hasAttributes()) {
				foreach ($node->attributes as $attribute) {
					$attributes[$attribute->nodeName] = $attribute->nodeValue;
				}
			}

			ksort($attributes);

			$link = Tag::link($attributes);

			if (isset($link['rel']) && strpos($link['rel'], 'stylesheet') === 0) {
				$this->styles[] = $link;
			} else {
				$this->links[] = $link;
			}

			$node->parentNode->removeChild($node);
		}

		natcasesort($this->links);
	}

	/**
	 * @inheritdoc
	 */
	public function get(): array
	{
		return array_merge($this->links, $this->styles);
	}

}