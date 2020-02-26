<?php

namespace Twist\Model\Site\Assets;

use Twist\Library\Dom\Document;
use Twist\Library\Dom\Element;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Str;

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
	 * @inheritdoc
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

				if (Str::contains($name, ':title')) {
					$content = $this->getTitle();
				}

				$this->metas[$name] = $this->getTag($type, $name, $content);
			}

			$node->parentNode->removeChild($node);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function all(): array
	{
		$metas = Hook::apply('twist_site_metas', $this->metas);
		natcasesort($metas);

		return $metas;
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

	/**
	 * @return string
	 */
	protected function getTitle(): string
	{
		static $title;

		if ($title === null) {
			$title = Str::fromEntities(the_title_attribute(['echo' => false]));
		}

		return $title;
	}

}