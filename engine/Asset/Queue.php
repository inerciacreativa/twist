<?php

namespace Twist\Asset;

use Twist\Library\Data\Collection;
use Twist\Library\Hook\Hookable;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Arr;
use Twist\Library\Support\Data;
use Twist\Twist;

/**
 * Class Queue
 *
 * @package Twist\Asset
 */
class Queue
{

	use Hookable;

	/**
	 * @var Collection
	 */
	private $styles;

	/**
	 * @var Collection
	 */
	private $scripts;

	/**
	 * @var array
	 */
	private $inline = [];

	/**
	 * AssetsQueue constructor.
	 */
	public function __construct()
	{
		$this->styles  = new Collection();
		$this->scripts = new Collection();

		$this->hook()
			 ->after('wp_enqueue_scripts', 'addStyles')
			 ->after('wp_enqueue_scripts', 'addScripts')
			 ->after('script_loader_tag', 'addScriptsAttributes', ['arguments' => 2])
			 ->after('wp_footer', 'addInlineScripts');
	}

	/**
	 * [
	 *    'id'     (string)
	 *    'load'   (string|bool)
	 *    'parent' (bool)
	 *    'deps'   (array)
	 *    'footer' (bool)
	 *    'attr'   (string)
	 * ]
	 *
	 * @param array $styles
	 * @param bool  $parent
	 *
	 * @return $this
	 */
	public function styles(array $styles, bool $parent = false): self
	{
		$this->styles = $this->addAssets($this->styles, $styles, $parent);

		return $this;
	}

	/**
	 * [
	 *    'id'     (string)
	 *    'load'   (string|bool)
	 *    'parent' (bool)
	 *    'deps'   (array)
	 *    'media'  (string)
	 * ]
	 *
	 * @param array $scripts
	 * @param bool  $parent
	 *
	 * @return $this
	 */
	public function scripts(array $scripts, bool $parent = false): self
	{
		$this->scripts = $this->addAssets($this->scripts, $scripts, $parent);

		return $this;
	}

	/**
	 * @param string|callable $script
	 *
	 * @return $this
	 */
	public function inline($script): self
	{
		$this->inline[] = $script;

		return $this;
	}

	/**
	 * Enqueue styles.
	 */
	private function addStyles(): void
	{
		$this->styles->each(static function ($style) {
			$load = Data::value($style['load']);

			if (is_string($load)) {
				$url = Twist::manifest()->url($load, $style['parent']);

				wp_deregister_style($style['id']);
				wp_enqueue_style($style['id'], $url, $style['deps'], null, $style['media']);
			} else if ($load) {
				wp_enqueue_style($style['id']);
			} else {
				wp_dequeue_style($style['id']);
			}
		});
	}

	/**
	 * Enqueue scripts.
	 */
	private function addScripts(): void
	{
		$this->scripts->each(static function ($script) {
			$load = Data::value($script['load']);

			if (is_string($load)) {
				$url = Twist::manifest()->url($script['load'], $script['parent']);

				wp_deregister_script($script['id']);
				wp_enqueue_script($script['id'], $url, $script['deps'], null, $script['footer']);
			} else if ($load) {
				if ($script['footer']) {
					wp_script_add_data($script['id'], 'group', 1);
				}
				wp_enqueue_script($script['id']);
			} else {
				wp_dequeue_script($script['id']);
			}
		});
	}

	/**
	 * Adds extra HTML attributes to script elements.
	 *
	 * @param string $script
	 * @param string $handle
	 *
	 * @return string|Tag
	 */
	private function addScriptsAttributes(string $script, string $handle): string
	{
		$scripts = $this->scripts->filter(static function ($script) {
			return !empty($script['attr']);
		})->pluck('attr', 'id')->all();

		if (array_key_exists($handle, $scripts) && ($tag = Tag::parse($script))) {
			$attribute       = $scripts[$handle];
			$tag[$attribute] = $attribute;
			$script          = (string) $tag;
		}

		return $script;
	}

	/**
	 * Adds inline script in the footer.
	 */
	private function addInlineScripts(): void
	{
		foreach ($this->inline as $script) {
			$script = Data::value($script);

			echo <<<SCRIPT
	<script>
$script
   </script>
SCRIPT;
		}
	}

	/**
	 * @param Collection $collection
	 * @param array      $assets
	 * @param bool       $parent
	 *
	 * @return Collection
	 */
	private function addAssets(Collection $collection, array $assets, bool $parent): Collection
	{
		if (Arr::isAssoc($assets)) {
			$assets = [$assets];
		}

		$assets = array_map(static function (array $asset) use ($parent) {
			return array_merge([
				'id'     => null,
				'load'   => false,
				'parent' => $parent,
				'deps'   => [],
				'attr'   => null, // only for scripts
				'footer' => true,
				'media'  => 'all', // only for styles
			], $asset);
		}, $assets);

		return $this->addToCollection($collection, $assets);
	}

	/**
	 * @param Collection $collection
	 * @param array      $array
	 *
	 * @return Collection
	 */
	private function addToCollection(Collection $collection, array $array): Collection
	{
		return Collection::make($array)->merge($collection)->unique('id');
	}

}
