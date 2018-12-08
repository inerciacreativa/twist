<?php

namespace Twist\Model\Site\Elements;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Str;
use Twist\Library\Html\Tag;

/**
 * Class Metas
 *
 * @package Twist\Model\Site\Elements
 */
class Metas implements ElementsInterface
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
		sort($this->metas);

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
			$title = html_entity_decode(the_title_attribute(['echo' => false]), ENT_HTML5 | ENT_QUOTES);
		}

		return $title;
	}

}