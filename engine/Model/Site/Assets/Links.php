<?php

namespace Twist\Model\Site\Assets;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;

/**
 * Class Links
 *
 * @package Twist\Model\Site\Assets
 */
class Links implements AssetsInterface
{

	/**
	 * @var array
	 */
	private $links = [];

	/**
	 * @var array
	 */
	private $styles = [];

	/**
	 * @inheritDoc
	 */
	public function get(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('link');

		while ($node = $nodes->item(0)) {
			$attributes = [];

			if ($node->hasAttributes()) {
				foreach ($node->attributes as $attribute) {
					$attributes[$attribute->nodeName] = $attribute->nodeValue;
				}
			}

			$node->parentNode->removeChild($node);

			if (empty($attributes)) {
				continue;
			}

			ksort($attributes);

			if (isset($attributes['type']) && $attributes['type'] === 'text/css') {
				unset($attributes['type']);
			}

			$link = Tag::link($attributes);

			if (isset($link['rel']) && strpos($link['rel'], 'stylesheet') === 0) {
				$this->styles[] = $link;
			} else {
				$this->links[] = $link;
			}

		}
	}

	/**
	 * @inheritDoc
	 */
	public function all(): array
	{
		$links  = Hook::apply('twist_site_links', $this->links);
		$styles = Hook::apply('twist_site_styles', $this->styles);

		return array_merge($links, $styles);
	}

}
