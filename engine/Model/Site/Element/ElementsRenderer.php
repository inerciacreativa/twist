<?php

namespace Twist\Model\Site\Element;

use Twist\Library\Hook\Hookable;

/**
 * Class ElementsRenderer
 *
 * @package Twist\Model\Site\Element
 */
class ElementsRenderer
{

	use Hookable;

	/**
	 * @var ElementsParser
	 */
	private $parser;

	/**
	 * Elements constructor.
	 *
	 * @param string         $hook
	 * @param ElementsParser $parser
	 */
	public function __construct(string $hook, ElementsParser $parser)
	{
		$this->parser = $parser;

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
	 * @return string
	 */
	public function render(): string
	{
		$html = [[]];

		foreach ($this->parser->all() as $element) {
			$html[] = $element->get();
		}

		return "\n\t" . implode("\n\t", array_merge(...$html)) . $this->parser->html();
	}

	/**
	 * @param string $html
	 */
	protected function parse(string $html): void
	{
		$this->parser->parse($html);
	}

}