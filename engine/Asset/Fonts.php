<?php

namespace Twist\Asset;

use Twist\App\App;
use Twist\Library\Hook\Hookable;
use Twist\Library\Support\Arr;

/**
 * Class Fonts
 *
 * @package Twist\Asset
 */
class Fonts
{

	use Hookable;

	/**
	 * @var Queue
	 */
	private $queue;

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
	 * @param Queue $queue
	 */
	public function __construct(Queue $queue)
	{
		$this->queue = $queue;

		$this->hook()->on(App::BOOT, 'addFonts');
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
	 *
	 */
	private function addFonts(): void
	{
		if ($this->loader) {
			$this->addScript($this->script, $this->fonts);
		} else if (array_key_exists('google', $this->fonts)) {
			$this->addStyle($this->fonts['google']->families);
		}
	}

	/**
	 * @param string $script
	 * @param array  $families
	 */
	private function addScript(string $script, array $families): void
	{
		$fonts = str_replace('"', "'", json_encode($families));

		$this->queue->inline("(function(i,s,o,g,r,a,m) {i['WebFontConfig']=r;
				      a=s.createElement(o);a.src=g;a.async=1;a.crossOrigin='anonymous';
				      m=s.getElementsByTagName(o)[0];m.parentNode.insertBefore(a,m);
				   })(window,document,'script','$script',$fonts);");
	}

	/**
	 * @param array $families
	 */
	private function addStyle(array $families): void
	{
		$fonts = implode('|', $families);

		$this->queue->styles([
			'id'   => 'fonts',
			'load' => "https://fonts.googleapis.com/css?family=$fonts",
		]);
	}
}
