<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Dom\Document;
use Twist\Library\Util\Str;

/**
 * Class ElementParser
 *
 * @package Twist\Model\Site\Element
 */
class ElementParser
{

	/**
	 * @var ElementInterface[]
	 */
	protected $elements;

	/**
	 * @var string
	 */
	protected $html;

	/**
	 * ElementParser constructor.
	 *
	 * @param array $elements
	 */
	public function __construct(array $elements)
	{
		foreach ($elements as $element) {
			$this->elements[] = new $element($this);
		}
	}

	/**
	 * @param string $html
	 */
	public function parse(string $html): void
	{
		if (empty($html)) {
			return;
		}

		$dom = new Document($html);
		$dom->loadMarkup($html);
		$dom->cleanComments();

		foreach ($this->elements as $element) {
			$element->parse($dom);
		}

		$this->html = trim($dom->saveMarkup());
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$html = [[]];

		foreach ($this->elements as $element) {
			$html[] = $element->get();
		}

		return "\n\t" . implode("\n\t", array_merge(...$html)) . $this->html;
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	public function clean(string $content): string
	{
		$content = Str::fromEntities($content);
		$content = str_replace([
			'//<![CDATA[',
			'//]]>',
			'/* <![CDATA[ */',
			'/* ]]> */',
		], '', $content);
		$content = preg_replace('/^\s+/m', "\t\t", $content);
		$content = trim($content);
		$content = "\n\t\t" . $content . "\n\t";

		return $content;
	}

}