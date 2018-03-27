<?php

namespace Twist\App;

use Twist\Model\Site\Site;
use Twist\View\Twig\TwigService;
use Twist\Library\Data\Collection;
use Twist\Library\Util\Arr;
use Twist\Library\Util\Data;
use Twist\Library\Util\Tag;
use function Twist\app;
use function Twist\config;
use function Twist\asset_url;

/**
 * Class Theme
 *
 * @package Twist\App
 */
class Theme
{

	/**
	 * @var array
	 */
	protected $services = [];

	/**
	 * @var array
	 */
	protected $config = [];

	/**
	 * @var Collection
	 */
	protected $styles;

	/**
	 * @var Collection
	 */
	protected $scripts;

	/**
	 * @var Collection
	 */
	protected $sidebars;

	/**
	 * @var array
	 */
	protected $fonts = [
		'families' => [],
		'script'   => '//ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js',
	];

	/**
	 * @var array
	 */
	protected $resources = [
		'preconnect'   => [
			['href' => '//fonts.gstatic.com', 'crossorigin' => true],
		],
		'dns-prefetch' => [
			'//ajax.googleapis.com',
		],
	];

	/**
	 * @var array
	 */
	protected $logo = [];

	/**
	 * @var array
	 */
	protected $menus = [];

	/**
	 * @var array
	 */
	protected $thumbnail = [];

	/**
	 * @var array
	 */
	protected $contact = [];

	/**
	 * Theme constructor.
	 */
	public function __construct()
	{
		$this->styles   = new Collection();
		$this->scripts  = new Collection();
		$this->sidebars = new Collection();

		add_filter('after_setup_theme', [$this, 'setup'], PHP_INT_MIN);

		add_filter('wp_enqueue_scripts', [$this, 'addStyles'], 999);
		add_filter('wp_enqueue_scripts', [$this, 'addScripts'], 999);
		add_filter('script_loader_tag', [$this, 'addScriptsAttributes'], 999, 2);
		add_filter('wp_resource_hints', [$this, 'addResourceHints'], 999, 2);
		add_filter('widgets_init', [$this, 'addSidebars'], 1);
		add_filter('user_contactmethods', [$this, 'addContactMethods'], 1);
		add_filter('wp_footer', [$this, 'addWebFonts'], PHP_INT_MAX);
	}

	/**
	 * @param ServiceProviderInterface $services
	 *
	 * @return $this
	 */
	public function services(ServiceProviderInterface $services): self
	{
		$this->services[] = $services;

		return $this;
	}

	/**
	 * @param array $config
	 *
	 * @return $this
	 */
	public function config(array $config): self
	{
		$this->config = array_merge($this->config, $config);

		return $this;
	}

	/**
	 * @param array $styles
	 *
	 * @return $this
	 */
	public function styles(array $styles): self
	{
		$this->styles = $this->addToCollection($this->styles, $styles);

		return $this;
	}

	/**
	 * @param array $scripts
	 *
	 * @return $this
	 */
	public function scripts(array $scripts): self
	{
		$this->scripts = $this->addToCollection($this->scripts, $scripts);

		return $this;
	}

	/**
	 * @param array       $families
	 * @param string|null $script
	 *
	 * @return $this
	 */
	public function webfonts(array $families, string $script = null): self
	{
		$this->fonts['families'] = $families;
		if ($script) {
			$this->fonts['script'] = $script;
		}

		return $this;
	}

	/**
	 * @param array $resources
	 *
	 * @return $this
	 */
	public function resources(array $resources): self
	{
		$this->resources = array_merge_recursive($this->resources, $resources);

		return $this;
	}

	/**
	 * @param array $sidebars
	 *
	 * @return $this
	 */
	public function sidebars(array $sidebars): self
	{
		$this->sidebars = $this->addToCollection($this->sidebars, $sidebars);

		return $this;
	}

	/**
	 * @see \add_theme_support()
	 *
	 * @param array $logo
	 *   [
	 *   'height' => (int)
	 *   'width' => (int)
	 *   'flex-width' => (bool)
	 *   'flex-height' => (bool)
	 *   'header-text' => (string)
	 *   ]
	 *
	 * @return $this
	 */
	public function logo(array $logo): self
	{
		$this->logo = $logo;

		return $this;
	}

	/**
	 * @see \register_nav_menus()
	 *
	 * @param array $menus
	 *
	 * @return $this
	 */
	public function menus(array $menus): self
	{
		$this->menus = array_merge($this->menus, $menus);

		return $this;
	}

	/**
	 * @param int  $width
	 * @param int  $height
	 * @param bool $crop
	 *
	 * @return $this
	 */
	public function thumbnail(int $width, int $height, bool $crop = false): self
	{
		$this->thumbnail['width']  = $width;
		$this->thumbnail['height'] = $height;
		$this->thumbnail['crop']   = $crop;

		return $this;
	}

	/**
	 * @see filter 'user_contactmethods'
	 *
	 * @param array $contact
	 *
	 * @return $this
	 */
	public function contact(array $contact): self
	{
		$this->contact = array_merge($this->contact, $contact);

		return $this;
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Pimple\Exception\FrozenServiceException
	 */
	public function setup()
	{
		$this->addConfig();
		$this->addServices();
		$this->addThemeSupport();
		$this->addHeadCleaner();

		app()->boot();
	}

	/**
	 * Adds config options.
	 */
	public function addConfig()
	{
		config()->fill([
			'app.debug'      => \defined('WP_DEBUG') && WP_DEBUG,
			'dir.stylesheet' => STYLESHEETPATH,
			'dir.template'   => TEMPLATEPATH,
			'dir.upload'     => wp_upload_dir()['basedir'],
			'uri.home'       => home_url(),
			'uri.stylesheet' => get_stylesheet_directory_uri(),
			'uri.template'   => get_template_directory_uri(),
		]);

		config()->fill([
			'view.service' => TwigService::id(),
			'view.theme'   => '',
			'view.data'    => ['site' => Site::class],
		]);

		do_action('ic_twist_theme', $this);

		config()->fill($this->config);

		config()->fill([
			'view.cache' => config('app.debug') ? false : config('dir.upload') . '/view_cache',
			'view.paths' => function () {
				if ($theme = config('view.theme')) {
					$theme = '/' . trim($theme . '/');
				}

				return array_unique(array_map(function ($path) use ($theme) {
					return file_exists("$path/views$theme") ? "$path/views$theme" : "$path/views";
				}, [STYLESHEETPATH, TEMPLATEPATH]));
			},
		]);
	}

	/**
	 * Adds service providers.
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Pimple\Exception\FrozenServiceException
	 */
	public function addServices()
	{
		foreach ($this->services as $service) {
			app()->provider($service);
		}
	}

	/**
	 * Registers theme support for several features.
	 */
	public function addThemeSupport()
	{
		add_theme_support('customize-selective-refresh-widgets');
		add_theme_support('title-tag');
		add_theme_support('html5', [
			'gallery',
			'caption',
		]);

		add_theme_support('post-thumbnails');
		add_theme_support('post-formats', [
			'aside',
			'image',
			'video',
			'quote',
			'link',
			'gallery',
			'audio',
		]);

		if (!empty($this->logo)) {
			add_theme_support('custom-logo', $this->logo);
		}

		if (!empty($this->thumbnail)) {
			set_post_thumbnail_size($this->thumbnail['width'], $this->thumbnail['height'], $this->thumbnail['crop']);
		}

		if (!empty($this->menus)) {
			register_nav_menus($this->menus);
		}
	}

	/**
	 * Enqueue styles.
	 */
	public function addStyles()
	{
		$this->styles->each(function ($style) {
			$load = Data::value(Arr::value($style, 'load'));

			if ($load) {
				if (\is_string($load)) {
					wp_enqueue_style($style['id'], asset_url($load, Arr::value($style, 'parent', false)), Arr::value($style, 'deps'), null);
				} else {
					wp_enqueue_style($style['id']);
				}
			} else {
				wp_dequeue_style($style['id']);
			}
		});
	}

	/**
	 * Enqueue scripts.
	 */
	public function addScripts()
	{
		$this->scripts->each(function ($script) {
			$load = Data::value(Arr::value($script, 'load'));

			if ($load) {
				if (\is_string($load)) {
					wp_enqueue_script($script['id'], asset_url($load, Arr::value($script, 'parent', false)), Arr::value($script, 'deps'), null, true);
				} else {
					wp_enqueue_script($script['id']);
				}
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
	 * @return string
	 */
	public function addScriptsAttributes(string $script, string $handle): string
	{
		$scripts = $this->scripts->filter(function ($script) {
			return isset($script['attr']);
		})->pluck('attr', 'id')->all();

		if (array_key_exists($handle, $scripts)) {
			$attribute = $scripts[$handle];
			$tag       = Tag::parse($script);

			if ($tag) {
				$tag[$attribute] = $attribute;
				$script          = (string) $tag;
			}
		}

		return $script;
	}

	/**
	 *  Add resource hints for Google fonts and scripts.
	 *
	 * @param array  $urls
	 * @param string $relation
	 *
	 * @return array
	 */
	public function addResourceHints(array $urls, string $relation): array
	{
		if (array_key_exists($relation, $this->resources)) {
			$urls = array_merge($urls, $this->resources[$relation]);
		}

		return $urls;
	}

	/**
	 * Register sidebars.
	 */
	public function addSidebars()
	{
		$this->sidebars->each(function ($sidebar) {
			if (Arr::has($sidebar, 'name')) {
				register_sidebar($sidebar);
			} else {
				unregister_sidebar($sidebar['id']);
			}
		});
	}

	/**
	 * Adds contact methods for user profiles.
	 *
	 * @param array $methods
	 *
	 * @return array
	 */
	public function addContactMethods(array $methods): array
	{
		return array_merge($methods, $this->contact);
	}

	/**
	 * Remove unnecessary elements in the header.
	 */
	public function addHeadCleaner()
	{
		add_filter('ic_twist_metas', function ($metas) {
			return array_filter($metas, function ($meta) {
				return !(isset($meta['name']) && $meta['name'] === 'generator');
			});
		});

		add_filter('ic_twist_header_links', function ($links) {
			return array_filter($links, function ($link) {
				return !\in_array($link['rel'], [
					'EditURI',
					'wlwmanifest',
				], false);
			});
		});

		add_filter('wpseo_json_ld_output', '__return_null');
	}

	/**
	 * Adds web fonts using Web Font Loader.
	 *
	 * @see https://github.com/typekit/webfontloader
	 */
	public function addWebFonts()
	{
		$families = implode("','", $this->fonts['families']);
		$script   = $this->fonts['script'];

		if (empty($families)) {
			return;
		}

		echo <<<SCRIPT
	<script>
	   WebFontConfig = {google: {families: ['$families']}};
	
	   (function(d) {
	      var wf = d.createElement('script'), s = d.scripts[0];
	      wf.src = '$script';
	      wf.async = true;
	      s.parentNode.insertBefore(wf, s);
	   })(document);
   </script>
SCRIPT;
	}

	/**
	 * @param Collection $collection
	 * @param array      $array
	 *
	 * @return Collection
	 */
	protected function addToCollection(Collection $collection, array $array): Collection
	{
		return (new Collection($array))->merge($collection)->unique('id');
	}

}