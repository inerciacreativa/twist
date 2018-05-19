<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Tag;

/**
 * Class Meta
 *
 * @package Twist\Model\Site\Element
 */
class Meta implements ElementInterface
{

	/**
	 * @var array
	 */
	protected $metas = [];

	/**
	 * @inheritdoc
	 */
	public function parse(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('meta');

		while ($node = $nodes->item(0)) {
			$content = trim($node->getAttribute('content'));

			if ($node->hasAttribute('name')) {
				$name = $node->getAttribute('name');

				$this->metas[$name] = Tag::meta([
					'name'    => $name,
					'content' => $content,
				]);
			} else if ($node->hasAttribute('property')) {
				$property = $node->getAttribute('property');

				$this->metas[$property] = Tag::meta([
					'property' => $property,
					'content'  => $content,
				]);
			}

			$node->parentNode->removeChild($node);
		}

		sort($this->metas);
	}

	/**
	 * @inheritdoc
	 */
	public function get(): array
	{
		return Hook::apply('twist_site_metas', $this->metas);
	}

}