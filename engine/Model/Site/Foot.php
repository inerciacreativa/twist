<?php

namespace Twist\Model\Site;

use Twist\Library\Hook\Hook;
use Twist\Model\Site\Element\ElementParser;
use Twist\Model\Site\Element\Script;

/**
 * Class Foot
 *
 * @package Twist\Model\Site
 */
class Foot
{

	public const HOOK = 'wp_footer';

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