<?php

namespace Twist\Model\Site\Elements;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;

/**
 * Class Scripts
 *
 * @package Twist\Model\Site\Elements
 */
class Scripts implements ElementsInterface
{

	/**
	 * @var array
	 */
	private $scripts = [];

	/**
	 * @inheritdoc
	 */
	public function get(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('script');

		while ($node = $nodes->item(0)) {
			$content    = empty($node->nodeValue) ? null : ElementsParser::clean($node->nodeValue);
			$attributes = [];

			if ($node->hasAttributes()) {
				foreach ($node->attributes as $attribute) {
					$attributes[$attribute->nodeName] = $attribute->nodeValue ?: $attribute->nodeName;
				}
			}

			$node->parentNode->removeChild($node);

			ksort($attributes);

			if (isset($attributes['src'])) {
				$attributes['src'] = htmlspecialchars($attributes['src']);
				$this->scripts[]   = Tag::make('script', $attributes);
			} else if ($content) {
				$this->scripts[] = Tag::make('script', $attributes, $content);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function all(): array
	{
		return Hook::apply('twist_site_scripts', $this->scripts);
	}

}