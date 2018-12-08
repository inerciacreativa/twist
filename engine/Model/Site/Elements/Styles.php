<?php

namespace Twist\Model\Site\Elements;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Tag;

/**
 * Class Styles
 *
 * @package Twist\Model\Site\Elements
 */
class Styles implements ElementsInterface
{

	/**
	 * @var array
	 */
	private $styles = [];

	/**
	 * @inheritdoc
	 */
	public function get(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('style');

		while ($node = $nodes->item(0)) {
			$content = empty($node->nodeValue) ? null : ElementsParser::clean($node->nodeValue);
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
	 * @inheritdoc
	 */
	public function all(): array
	{
		return Hook::apply('twist_site_styles', $this->styles);
	}

}