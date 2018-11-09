<?php

namespace Twist\Model\Site;

use Twist\Library\Hook\Hook;
use Twist\Model\Site\Element\ElementParser;
use Twist\Model\Site\Element\Link;
use Twist\Model\Site\Element\Meta;
use Twist\Model\Site\Element\Script;
use Twist\Model\Site\Element\Style;
use Twist\Model\Site\Element\Title;

/**
 * Class Head
 *
 * @package Twist\Model\Site
 */
class Head
{

	public const HOOK = 'wp_head';

	/**
	 * @var ElementParser
	 */
	protected $parser;

	/**
	 * @var string
	 */
	protected $html;

	/**
	 * Head constructor.
	 */
	public function __construct()
	{
		$this->parser = new ElementParser([
			Title::class,
			Meta::class,
			Link::class,
			Style::class,
			Script::class,
		]);

		Hook::bind($this)->capture(self::HOOK, 'parse')->fire(self::HOOK);
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
		return $this->parser->render();
	}

	/**
	 * @param string $html
	 */
	protected function parse(string $html): void
	{
		$this->parser->parse($html);
	}

}