<?php

namespace Twist\Model\Site\Assets;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hookable;
use Twist\Library\Support\Str;
use Twist\Model\Site\Site;

/**
 * Class AssetsGroup
 *
 * @package Twist\Model\Site\Assets
 */
class AssetsGroup
{

	use Hookable;

	/**
	 * @var AssetsInterface[]
	 */
	private $assets;

	/**
	 * @var string
	 */
	private $html;

	/**
	 * Collection constructor.
	 *
	 * @param string $hook
	 * @param array  $classes
	 */
	public function __construct(string $hook, array $classes)
	{
		foreach ($classes as $class) {
			$this->add(new $class());
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
	 * @return string
	 */
	public function render(): string
	{
		$html = [[]];

		foreach ($this->assets as $assets) {
			$html[] = $assets->all();
		}

		return "\n\t" . implode("\n\t", array_merge(...$html)) . $this->html;
	}

	/**
	 * @return AssetsInterface[]
	 */
	public function all(): array
	{
		return $this->assets;
	}

	/**
	 * @return string
	 */
	public function html(): string
	{
		return $this->html;
	}

	/**
	 * @param AssetsInterface $assets
	 */
	protected function add(AssetsInterface $assets): void
	{
		$this->assets[] = $assets;
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

		foreach ($this->assets as $assets) {
			$assets->get($dom);
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
