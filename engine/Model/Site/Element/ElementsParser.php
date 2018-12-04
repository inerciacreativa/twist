<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;
use Twist\Library\Util\Str;
use Twist\Model\Site\Site;

/**
 * Class ElementsParser
 *
 * @package Twist\Model\Site\Element
 */
class ElementsParser
{

	/**
	 * @var ElementsInterface[]
	 */
	private $elements;

	/**
	 * @var string
	 */
	private $html;

	/**
	 * ElementParser constructor.
	 *
	 * @param array $elements
	 */
	public function __construct(array $elements)
	{
		foreach ($elements as $element) {
			$this->add(new $element());
		}
	}

	/**
	 * @param ElementsInterface $elements
	 *
	 * @return $this
	 */
	public function add(ElementsInterface $elements): self
	{
		$this->elements[] = $elements;

		return $this;
	}

	/**
	 * @param string $html
	 */
	public function parse(string $html): void
	{
		if (empty($html)) {
			return;
		}

		$dom = new Document(Site::language());
		$dom->loadMarkup($html);
		$dom->cleanComments();

		foreach ($this->elements as $element) {
			$element->extract($dom);
		}

		$this->html = trim($dom->saveMarkup());
	}

	/**
	 * @return ElementsInterface[]
	 */
	public function all(): array
	{
		return $this->elements;
	}

	/**
	 * @return string
	 */
	public function html(): string
	{
		return $this->html;
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	public static function clean(string $content): string
	{
		$content = Str::fromEntities($content);
		$content = str_replace([
			'//<![CDATA[',
			'//]]>',
			'/* <![CDATA[ */',
			'/* ]]> */',
		], '', $content);
		$content = (string) preg_replace('/^\s+/m', "\t\t", $content);
		$content = (string) preg_replace('/^([^\t])/m', "\t\t$1", $content);
		$content = trim($content);
		$content = "\n\t\t" . $content . "\n\t";

		return $content;
	}

}