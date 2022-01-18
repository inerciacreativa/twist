<?php

namespace Twist\Asset;

use Twist\App\Action;
use Twist\Library\Hook\Hookable;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Url;

/**
 * Class GoogleFonts
 *
 * @package Twist\Asset
 */
class GoogleFonts
{

	use Hookable;

	/**
	 * @var Resources
	 */
	private $resources;

	/**
	 * @var array
	 */
	private $families = [];

	/**
	 * GoogleFonts constructor.
	 *
	 * @param Resources $resources
	 */
	public function __construct(Resources $resources)
	{
		$this->resources = $resources;

		$this->hook()
		     ->on(Action::SETUP, 'addResources')
		     ->on('twist_site_links', 'addLink');
	}

	/**
	 * @param string|array $families
	 *
	 * @return $this
	 */
	public function add($families): self
	{
		foreach ((array) $families as $family) {
			if ($this->getApiVersion($family) < 2) {
				$family = $this->fromApi1ToApi2($family);
			}

			$this->families[] = $family;
		}

		return $this;
	}

	/**
	 * @param string $family
	 *
	 * @return int
	 */
	protected function getApiVersion(string $family): int
	{
		[$name, $styles] = explode(':', $family);

		if (empty($styles) || strpos($styles, '@') !== false) {
			return 2;
		}

		return 1;
	}

	/**
	 * @param string $family
	 *
	 * @return string
	 */
	protected function fromApi1ToApi2(string $family): string
	{
		[$name, $styles] = explode(':', $family);
		$styles = explode(',', $styles);
		$weight = [];
		$italic = [];

		foreach ($styles as $style) {
			if (is_numeric($style)) {
				$weight[] = $style;
			} else {
				$italic[] = (int) $style;
			}
		}

		sort($weight, SORT_NUMERIC);
		sort($italic, SORT_NUMERIC);

		if (!empty($italic)) {
			$weight = array_map(static function ($item) {
				return "0,$item";
			}, $weight);
			$italic = array_map(static function ($item) {
				return "1,$item";
			}, $italic);

			$ranges = 'ital,wght@' . implode(';', [...$weight, ...$italic]);
		} else {
			$ranges = 'wght@' . implode(';', $weight);
		}

		return "$name:$ranges";
	}

	/**
	 *
	 */
	protected function addResources(): void
	{
		if (empty($this->families)) {
			return;
		}

		$this->resources->add('preconnect', 'https://fonts.googleapis.com');
		$this->resources->add('preconnect', 'https://fonts.gstatic.com');
	}

	/**
	 * @param array $links
	 *
	 * @return array
	 */
	protected function addLink(array $links): array
	{
		if (!empty($this->families)) {
			$links[] = $this->getPreloadLink();
			$links[] = $this->getStyleLink();
		}

		return $links;
	}

	/**
	 * @return Tag
	 */
	protected function getPreloadLink(): Tag
	{
		return Tag::link([
			'href'        => $this->getUrl(),
			'rel'         => 'preload',
			'as'          => 'style',
			'crossorigin' => true,
		]);
	}

	/**
	 * @return Tag
	 */
	protected function getStyleLink(): Tag
	{
		return Tag::link([
			'href'        => $this->getUrl(),
			'media'       => 'print',
			'rel'         => 'stylesheet',
			'crossorigin' => true,
			'onload'      => "this.media='all'",
		]);
	}

	/**
	 * @return string
	 */
	protected function getUrl(): string
	{
		return $this->getUrlApi2();
	}

	/**
	 * Load fonts from API v1
	 *
	 * @return string
	 */
	/** @noinspection SuspiciousAssignmentsInspection */
	protected function getUrlApi1(): string
	{
		$url        = new Url('https://fonts.googleapis.com/css');
		$url->query = ['family' => implode('|', $this->families)];
		$url->query = ['display' => 'swap'];

		return $url;
	}

	/**
	 * Load fonts from API v2
	 *
	 * @return string
	 */
	protected function getUrlApi2(): string
	{
		$url   = 'https://fonts.googleapis.com/css2';
		$query = '?family=' . implode('&family=', $this->families);

		return $url . $query . '&display=swap';
	}

}
