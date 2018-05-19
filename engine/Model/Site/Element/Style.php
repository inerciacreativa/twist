<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;
use Twist\Library\Util\Tag;

/**
 * Class Style
 *
 * @package Twist\Model\Site\Element
 */
class Style implements ElementInterface
{

	/**
	 * @var ElementParser
	 */
	protected $parser;

	/**
	 * @var array
	 */
	protected $styles = [];

	/**
	 * Style constructor.
	 *
	 * @param ElementParser $parser
	 */
	public function __construct(ElementParser $parser)
	{
		$this->parser = $parser;
	}

	/**
	 * @inheritdoc
	 */
	public function parse(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('style');

		while ($node = $nodes->item(0)) {
			$content = empty($node->nodeValue) ? null : $this->parser->clean($node->nodeValue);

			if ($content) {
				$attributes = [];

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
	public function get(): array
	{
		return $this->styles;
	}

}