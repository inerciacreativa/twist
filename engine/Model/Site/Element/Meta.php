<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Str;
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
			$tag = null;
			if ($node->hasAttribute('name')) {
				$tag = 'name';
			} else if ($node->hasAttribute('property')) {
				$tag = 'property';
			}

			if ($tag) {
				$name = $node->getAttribute($tag);

				if (Str::contains($name, ':title')) {
					$content = $this->title();
				} else {
					$content = trim($node->getAttribute('content'));
				}

				$this->metas[$name] = Tag::meta([
					'name'    => $name,
					'content' => $content,
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

	/**
	 * @return string
	 */
	protected function title(): string
	{
		return html_entity_decode(the_title_attribute(['echo' => false]), ENT_HTML5 | ENT_QUOTES);
	}

}