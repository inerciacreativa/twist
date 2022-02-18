<?php

namespace Twist\Model\Site\Assets;

use Twist\Library\Dom\Document;
use Twist\Library\Dom\Element;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;

/**
 * Class Metas
 *
 * @package Twist\Model\Site\Assets
 */
class Metas implements AssetsInterface
{

	/**
	 * @var array
	 */
	private $metas = [];

	/**
	 * @inheritDoc
	 */
	public function get(Document $dom): void
	{
		$nodes = $dom->getElementsByTagName('meta');

		/** @var Element $node */
		while ($node = $nodes->item(0)) {
			$type = null;
			if ($node->hasAttribute('name')) {
				$type = 'name';
			} else if ($node->hasAttribute('property')) {
				$type = 'property';
			}

			if ($type) {
				$name    = $node->getAttribute($type);
				$content = trim($node->getAttribute('content'));

				$this->metas[$name] = $this->getTag($type, $name, $content);
			}

			$node->parentNode->removeChild($node);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function all(): array
	{
		return Hook::apply('twist_site_metas', $this->metas);
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $content
	 *
	 * @return Tag
	 */
	protected function getTag(string $type, string $name, string $content): Tag
	{
		$filter = str_replace(':', '_', "twist_site_meta_$name");
		$meta   = Tag::meta([
			$type     => $name,
			'content' => $content,
		]);

		return Hook::apply($filter, $meta);
	}

}
