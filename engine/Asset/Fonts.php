<?php

namespace Twist\Asset;

use Twist\App\Action;
use Twist\Library\Hook\Hookable;
use Twist\Library\Support\Arr;
use Twist\Library\Support\Url;

/**
 * Class Fonts
 *
 * @package Twist\Asset
 */
class Fonts
{

	use Hookable;

	private $google = [
		'https://fonts.gstatic.com',
		'https://fonts.googleapis.com',
	];

	/**
	 * @var Queue
	 */
	private $queue;

	/**
	 * @var Resources
	 */
	private $resources;

	/**
	 * @var string
	 */
	private $script = 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.28/webfont.js';

	/**
	 * @var array
	 */
	private $fonts = [];

	/**
	 * @var bool
	 */
	private $loader = true;

	/**
	 * Fonts constructor.
	 *
	 * @param Queue     $queue
	 * @param Resources $resources
	 */
	public function __construct(Queue $queue, Resources $resources)
	{
		$this->queue     = $queue;
		$this->resources = $resources;

		$this->hook()->on(Action::BOOT, 'process');
	}

	/**
	 * @param array       $fonts
	 * @param string|bool $loader
	 *
	 * @return $this
	 */
	public function add(array $fonts, $loader = true): self
	{
		$this->fonts = Arr::map($fonts, static function ($fonts, $id) {
			if ($id === 'google') {
				$fonts = ['families' => $fonts];
			}

			return (object) $fonts;
		});

		if (is_string($loader)) {
			$this->script = $loader;
			$this->loader = true;
		} else {
			$this->loader = (bool) $loader;
		}

		return $this;
	}

	/**
	 * Process the font families.
	 */
	private function process(): void
	{
		$this->resources();

		if ($this->loader) {
			$this->script($this->script, $this->encode($this->fonts));
		} else if (array_key_exists('google', $this->fonts)) {
			$this->style($this->implode($this->fonts));
		}
	}

	/**
	 * Add the resource hints.
	 */
	private function resources(): void
	{
		if (array_key_exists('google', $this->fonts)) {
			foreach ($this->google as $resource) {
				$this->resources->add('preconnect', $resource);
			}
		}

		if ($this->loader) {
			$script = Url::parse($this->script);
			if (!$script->isLocal()) {
				$this->resources->add('preconnect', $script);
			}
		}
	}

	/**
	 * Prepare the fonts to use in the script.
	 *
	 * @param array $fonts
	 *
	 * @return string
	 */
	private function encode(array $fonts): string
	{
		return str_replace('"', "'", json_encode($fonts));
	}

	/**
	 * Add the Web Font Loader script.
	 *
	 * @param string $script
	 * @param string $fonts
	 *
	 * @link https://github.com/typekit/webfontloader
	 */
	private function script(string $script, string $fonts): void
	{
		$this->queue->inline('fonts', "(function(i,s,o,g,r,a,m) {i['WebFontConfig']=r;
				      a=s.createElement(o);a.src=g;a.async=1;a.crossOrigin='anonymous';
				      m=s.getElementsByTagName(o)[0];m.parentNode.insertBefore(a,m);
				   })(window,document,'script','$script',$fonts);");
	}

	/**
	 * Prepare the Google Fonts families to use in the stylesheet URL.
	 *
	 * @param array $fonts
	 *
	 * @return string
	 */
	private function implode(array $fonts): string
	{
		return implode('|', $fonts['google']->families);
	}

	/**
	 * Add the stylesheet for Google Fonts.
	 *
	 * @param string $fonts
	 */
	private function style(string $fonts): void
	{
		$this->queue->styles([
			'id'   => 'fonts',
			'load' => "https://fonts.googleapis.com/css?family=$fonts",
		]);
	}
}
