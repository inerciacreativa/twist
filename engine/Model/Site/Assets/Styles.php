<?php

namespace Twist\Model\Site\Assets;

use Twist\Library\Dom\Document;
use Twist\Library\Dom\Element;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;

/**
 * Class Styles
 *
 * @package Twist\Model\Site\Assets
 */
class Styles implements AssetsInterface
{

	/**
	 * @var array
	 */
	private $styles = [];

	/**
	 * @inheritDoc
	 */
	public function get(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('style');

		/** @var Element $node */
		while ($node = $nodes->item(0)) {
			$content    = empty($node->nodeValue) ? null : AssetsGroup::clean($node->nodeValue);
			$attributes = [];

			if ($content) {
				if ($node->hasAttributes()) {
					foreach ($node->attributes as $attribute) {
						$attributes[$attribute->nodeName] = $attribute->nodeValue ?: $attribute->nodeName;
					}
				}

				ksort($attributes);

				$this->styles[] = Tag::make('style', $attributes, $content);
			}

			$node->parentNode->removeChild($node);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function all(): array
	{
		return Hook::apply('twist_site_styles', $this->styles);
	}

}
