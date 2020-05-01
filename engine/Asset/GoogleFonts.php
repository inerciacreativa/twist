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
		$this->families = array_merge($this->families, (array) $families);

		return $this;
	}

	/**
	 *
	 */
	protected function addResources(): void
	{
		if (empty($this->families)) {
			return;
		}

		$this->resources->add('dns-prefetch', 'https://fonts.googleapis.com');
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
			'href'        => $this->getUrl(true),
			'media'       => 'print',
			'rel'         => 'stylesheet',
			'crossorigin' => true,
			'onload'      => "this.media='all'",
		]);
	}

	/**
	 * @param bool $style
	 *
	 * @return string
	 */
	protected function getUrl(bool $style = false): string
	{
		$url = new Url('https://fonts.googleapis.com/css');

		$url->query = ['family' => implode('|', $this->families)];
		if ($style) {
			$url->query = ['display' => 'swap'];
		}

		return $url;
	}

}
