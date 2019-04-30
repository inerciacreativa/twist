<?php

namespace Twist\Model\Site\Elements;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hookable;
use Twist\Library\Support\Str;
use Twist\Model\Site\Site;

/**
 * Class ElementsParser
 *
 * @package Twist\Model\Site\Elements
 */
class ElementsParser
{

	use Hookable;

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
	 * @param string $hook
	 * @param array  $elements
	 */
	public function __construct(string $hook, array $elements)
	{
		foreach ($elements as $element) {
			$this->add(new $element());
		}

		$this->hook()->capture($hook, 'parse')->fire($hook);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->render();
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
	 * @return string
	 */
	public function render(): string
	{
		$html = [[]];

		foreach ($this->elements as $elements) {
			$html[] = $elements->all();
		}

		return "\n\t" . implode("\n\t", array_merge(...$html)) . $this->html;
	}

	/**
	 * @param ElementsInterface $elements
	 */
	protected function add(ElementsInterface $elements): void
	{
		$this->elements[] = $elements;
	}

	/**
	 * @param string $html
	 */
	protected function parse(string $html): void
	{
		if (empty($html)) {
			return;
		}

		$dom = new Document(Site::language());
		$dom->loadMarkup($html);
		$dom->removeComments();

		foreach ($this->elements as $elements) {
			$elements->get($dom);
		}

		$this->html = trim($dom->saveMarkup());
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
		$content = preg_replace('/^\s+/m', "\t\t", $content);
		$content = preg_replace('/^([^\t])/m', "\t\t$1", $content);
		$content = trim($content);
		$content = "\n\t\t" . $content . "\n\t";

		return $content;
	}

}