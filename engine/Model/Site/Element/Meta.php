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
	 * @var string
	 */
	protected $title;

	/**
	 * @inheritdoc
	 */
	public function parse(Document $dom): void
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
	 * @param string $type
	 * @param string $name
	 * @param string $content
	 *
	 * @return Tag
	 */
	protected function tag(string $type, string $name, string $content): Tag
	{
		$filter = str_replace(':', '_', "twist_meta_$name");
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