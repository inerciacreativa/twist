<?php

namespace Twist\Model\Site\Assets;

use Twist\Library\Dom\Document;
use Twist\Library\Dom\Element;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;

/**
 * Class Scripts
 *
 * @package Twist\Model\Site\Assets
 */
class Scripts implements AssetsInterface
{

	/**
	 * @var array
	 */
	private $scripts = [];

	/**
	 * @inheritDoc
	 */
	public function get(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('script');

		/** @var Element $node */
		while ($node = $nodes->item(0)) {
			$content    = empty($node->nodeValue) ? null : AssetsGroup::clean($node->nodeValue);
			$attributes = [];

			if ($node->hasAttributes()) {
				foreach ($node->attributes as $attribute) {
					$attributes[$attribute->nodeName] = $attribute->nodeValue ?: $attribute->nodeName;
				}
			}

			$node->parentNode->removeChild($node);

			ksort($attributes);

			if (isset($attributes['type']) && $attributes['type'] === 'text/javascript') {
				unset($attributes['type']);
			}

			if (isset($attributes['src'])) {
				$attributes['src'] = htmlspecialchars($attributes['src']);
				$this->scripts[]   = Tag::make('script', $attributes);
			} else if ($content) {
				$this->scripts[] = Tag::make('script', $attributes, $content);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function all(): array
	{
		return Hook::apply('twist_site_scripts', $this->scripts);
	}

}
