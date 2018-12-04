<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Tag;

/**
 * Class Script
 *
 * @package Twist\Model\Site\Element
 */
class Script implements ElementsInterface
{

	/**
	 * @var array
	 */
	private $scripts = [];

	/**
	 * @inheritdoc
	 */
	public function extract(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('script');

		while ($node = $nodes->item(0)) {
			$attributes = [];

			if ($node->hasAttributes()) {
				foreach ($node->attributes as $attribute) {
					$attributes[$attribute->nodeName] = $attribute->nodeValue ?: $attribute->nodeName;
				}
			}

			ksort($attributes);

			if (isset($attributes['src'])) {
				$attributes['src'] = htmlspecialchars($attributes['src']);
				$this->scripts[]   = Tag::make('script', $attributes);
			} else {
				$content = empty($node->nodeValue) ? null : ElementsParser::clean($node->nodeValue);

				if ($content) {
					$this->scripts[] = Tag::make('script', $attributes, $content);
				}
			}

			$node->parentNode->removeChild($node);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function get(): array
	{
		return Hook::apply('twist_site_scripts', $this->scripts);
	}

}