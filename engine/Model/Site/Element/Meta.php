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
class Meta implements ElementsInterface
{

	/**
	 * @var array
	 */
	private $metas = [];

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @inheritdoc
	 */
	public function extract(Document $dom): void
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
				$name = $node->getAttribute($type);

				if (Str::contains($name, ':title')) {
					$content = $this->title();
				} else {
					$content = trim($node->getAttribute('content'));
				}

				$this->metas[$name] = $this->tag($type, $name, $content);
			}

			$node->parentNode->removeChild($node);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function get(): array
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
	protected function tag(string $type, string $name, string $content): Tag
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
	protected function title(): string
	{
		if ($this->title === null) {
			$this->title = html_entity_decode(the_title_attribute(['echo' => false]), ENT_HTML5 | ENT_QUOTES);
		}

		return $this->title;
	}

}