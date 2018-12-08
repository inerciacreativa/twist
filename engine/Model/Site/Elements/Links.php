<?php

namespace Twist\Model\Site\Elements;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Tag;

/**
 * Class Links
 *
 * @package Twist\Model\Site\Elements
 */
class Links implements ElementsInterface
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
	 * @inheritdoc
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

			$link = Tag::link($attributes);

			if (isset($link['rel']) && strpos($link['rel'], 'stylesheet') === 0) {
				$this->styles[] = $link;
			} else {
				$this->links[] = $link;
			}

		}
	}

	/**
	 * @inheritdoc
	 */
	public function all(): array
	{
		$links  = Hook::apply('twist_site_links', $this->links);
		$styles = Hook::apply('twist_site_styles', $this->styles);
		$all    = array_merge($links, $styles);
		natcasesort($all);

		return $all;
	}

}