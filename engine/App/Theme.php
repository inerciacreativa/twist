<?php

namespace Twist\App;

use Closure;
use Twist\Library\Data\Collection;
use Twist\Library\Hook\Hookable;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Arr;
use Twist\Library\Support\Data;
use Twist\Service\ServiceProviderInterface;
use Twist\Twist;
use Twist\View\Twig\TwigView;

/**
 * Class Theme
 *
 * @package Twist\App
 */
class Theme
{

	use Hookable;

	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var bool
	 */
	protected $parent = true;

	/**
	 * @var Closure
	 */
	protected $setup;

	/**
	 * @var array
	 */
	protected $services = [];

	/**
	 * @var array
	 */
	protected $options = [];

	/**
	 * @var Collection
	 */
	protected $styles;

	/**
	 * @var Collection
	 */
	protected $scripts;

	/**
	 * @var array
	 */
	protected $inline = [];

	/**
	 * @var array
	 */
	protected $links = [];

	/**
	 * @var array
	 */
	protected $metas = [];

	/**
	 * @var Collection
	 */
	protected $sidebars;

	/**
	 * @var array
	 */
	protected $fonts = [
		'config' => [],
		'loader' => 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js',
	];

	/**
	 * @var array
	 */
	protected $resources = [];

	/**
	 * @var array
	 */
	protected $logo = [];

	/**
	 * @var array
	 */
	protected $images = [];

	/**
	 * @var array
	 */
	protected $menus = [];

	/**
	 * @var array
	 */
	protected $contact = [];

	/**
	 * @var array
	 */
	protected $formats = [];

	/**
	 * Theme constructor.
	 *
	 * @param App    $app
	 * @param Config $config
	 */
	public function __construct(App $app, Config $config)
	{
		$this->app    = $app;
		$this->config = $config;

		$this->styles   = new Collection();
		$this->scripts  = new Collection();
		$this->sidebars = new Collection();

		$this->hook()
		     ->before('after_setup_theme', 'boot')
		     ->on('show_admin_bar', '__return_false')
		     ->on('user_contactmethods', 'addContactMethods')
		     ->on('wp_enqueue_scripts', 'addStyles')
		     ->on('wp_enqueue_scripts', 'addScripts')
		     ->on('twist_site_links', 'addLinks')
		     ->on('twist_site_metas', 'addMetas')
		     ->on('widgets_init', 'addSidebars')
		     ->after('wp_footer', 'addInlineScripts')
		     ->after('script_loader_tag', 'addScriptsAttributes', ['arguments' => 2])
		     ->after('wp_resource_hints', 'addResourceHints', ['arguments' => 2]);
	}

	/**
	 * @param Closure $setup
	 *
	 * @return $this
	 */
	public function setup(Closure $setup): self
	{
		$this->setup = $setup;

		return $this;
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
	 * @param array $options
	 *
	 * @return $this
	 */
	public function options(array $options): self
	{
		$this->options = Arr::merge($this->options, $options);

		return $this;
	}

	public function assets(string $path, string $manifest): self
	{
		return $this->options([
			'asset' => [
				$this->parent ? Asset::PARENT : Asset::CHILD => [
					'path'     => '/' . trim($path, '/') . '/',
					'manifest' => $manifest,
				],
			],
		]);
	}

	/**
	 * @param array $styles
	 *
	 * @return $this
	 */
	public function styles(array $styles): self
	{
		$this->styles = $this->addAssets($this->styles, $styles);

		return $this;
	}

	/**
	 * @param array $scripts
	 *
	 * @return $this
	 */
	public function scripts(array $scripts): self
	{
		$this->scripts = $this->addAssets($this->scripts, $scripts);

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
	 * @param array $links
	 *
	 * @return $this
	 */
	public function links(array $links): self
	{
		$this->links = $links;

		return $this;
	}

	/**
	 * @param array $metas
	 *
	 * @return $this
	 */
	public function metas(array $metas): self
	{
		$this->metas = $metas;

		return $this;
	}

	/**
	 * @param array       $config
	 * @param string|bool $loader
	 *
	 * @return $this
	 */
	public function webfonts(array $config, $loader = true): self
	{
		$this->fonts['config'] = Arr::map($config, static function ($id, $config) {
			if ($id === 'google') {
				$config = ['families' => $config];
			}

			return (object) $config;
		});

		if (!$loader || is_string($loader)) {
			$this->fonts['loader'] = $loader;
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
		$this->resources = Arr::merge($this->resources, $resources);

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
	 * @see add_theme_support()
	 *
	 */
	public function logo(array $logo): self
	{
		$this->logo = $logo;

		return $this;
	}

	/**
	 * @param int  $width
	 * @param int  $height
	 * @param bool $crop
	 *
	 * @return $this
	 */
	public function thumbnail(int $width, int $height = 0, bool $crop = false): self
	{
		return $this->image('post-thumbnail', $width, $height, $crop);
	}

	/**
	 * @param string $name
	 * @param int    $width
	 * @param int    $height
	 * @param bool   $crop
	 *
	 * @return $this
	 */
	public function image(string $name, int $width, int $height = 0, bool $crop = false): self
	{
		if ($width === 0 && $height === 0) {
			unset($this->images[$name]);
		} else {
			$this->images[$name] = [$name, $width, $height, $crop];
		}

		return $this;
	}

	/**
	 * @param array $menus
	 *
	 * @return $this
	 * @see register_nav_menus()
	 *
	 */
	public function menus(array $menus): self
	{
		$this->menus = array_merge($this->menus, $menus);

		return $this;
	}

	/**
	 * @param array $contact
	 *
	 * @return $this
	 * @see filter 'user_contactmethods'
	 *
	 */
	public function contact(array $contact): self
	{
		$this->contact = array_merge($this->contact, $contact);

		return $this;
	}

	/**
	 * @param array $formats
	 *
	 * @return $this
	 */
	public function formats(array $formats): self
	{
		$this->formats = array_intersect($formats, [
			'aside',
			'image',
			'video',
			'quote',
			'link',
			'gallery',
			'audio',
		]);

		return $this;
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Pimple\Exception\FrozenServiceException
	 */
	protected function boot(): void
	{
		$this->addConfig();
		$this->addLanguages();
		$this->addThemeSupport();
		$this->addWebFonts();
		$this->addServices();

		$this->app->boot();
	}

	/**
	 * Adds config options.
	 */
	protected function addConfig(): void
	{
		$debug = defined('WP_DEBUG') && WP_DEBUG;

		$this->config->set([
			'app'  => [
				'debug' => $debug,
			],
			'dir'  => [
				'home'       => defined('WP_ROOT') ? WP_ROOT : ABSPATH,
				'stylesheet' => get_stylesheet_directory(),
				'template'   => get_template_directory(),
				'upload'     => wp_upload_dir()['basedir'],
			],
			'uri'  => [
				'home'       => home_url(),
				'stylesheet' => get_stylesheet_directory_uri(),
				'template'   => get_template_directory_uri(),
			],
			'view' => [
				'cache'     => !$debug,
				'service'   => TwigView::id(),
				'templates' => '/templates',
			],
		]);

		if ($setup = $this->setup) {
			$this->parent = false;
			$setup($this);
		}

		$this->config->set($this->options);

		$this->config->set([
			'view' => [
				'cache' => $this->config->get('view.cache') ? $this->config->get('dir.upload') . '/view_cache' : false,
				'paths' => array_unique(array_map(function ($path) {
					return $path . $this->config->get('view.templates');
				}, [
					$this->config->get('dir.stylesheet'),
					$this->config->get('dir.template'),
				])),
			],
		]);
	}

	/**
	 * Load translations.
	 */
	protected function addLanguages(): void
	{
		load_theme_textdomain('twist', $this->config->get('dir.template') . '/languages');
		if ($this->config->get('dir.template') !== $this->config->get('dir.stylesheet')) {
			load_theme_textdomain('twist', $this->config->get('dir.stylesheet') . '/languages');
		}
	}

	/**
	 * Adds service providers.
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Pimple\Exception\FrozenServiceException
	 */
	protected function addServices(): void
	{
		foreach ($this->services as $service) {
			$this->app->provider($service);
		}
	}

	/**
	 * Registers theme support for several features.
	 */
	protected function addThemeSupport(): void
	{
		add_theme_support('customize-selective-refresh-widgets');
		add_theme_support('automatic-feed-links');
		add_theme_support('title-tag');
		add_theme_support('html5', [
			'gallery',
			'caption',
		]);

		if (isset($this->images['post-thumbnail'])) {
			add_theme_support('post-thumbnails');
		}

		if (!empty($this->formats)) {
			add_theme_support('post-formats', $this->formats);
		}

		if (!empty($this->logo)) {
			add_theme_support('custom-logo', $this->logo);
		}

		foreach ($this->images as $image) {
			add_image_size(...$image);
		}

		if (!empty($this->menus)) {
			register_nav_menus($this->menus);
		}
	}

	/**
	 * Adds web fonts using a link to Google Fonts or Web Font Loader.
	 *
	 * @see https://github.com/typekit/webfontloader
	 */
	protected function addWebFonts(): void
	{
		if (!empty($this->fonts['config'])) {
			if (is_string($this->fonts['loader'])) {
				$script = $this->fonts['loader'];
				$config = str_replace('"', "'", json_encode($this->fonts['config']));

				$this->inline("(function(i,s,o,g,r,a,m) {i['WebFontConfig']=r;
				      a=s.createElement(o);a.src=g;a.async=1;a.crossOrigin='anonymous';
				      m=s.getElementsByTagName(o)[0];m.parentNode.insertBefore(a,m);
				   })(window,document,'script','$script',$config);");
			} else if (array_key_exists('google', $this->fonts['config'])) {
				$families = implode('|', $this->fonts['config']['google']->families);

				$this->styles([
					[
						'id'   => 'fonts',
						'load' => 'https://fonts.googleapis.com/css?family=' . $families,
					],
				]);
			}
		}
	}

	/**
	 * Enqueue styles.
	 */
	protected function addStyles(): void
	{
		$this->styles->each(static function ($style) {
			$load = Data::value(Arr::value($style, 'load'));

			if ($load) {
				if (is_string($load)) {
					wp_enqueue_style($style['id'], Twist::asset()
					                                    ->url($load, $style['parent']), $style['deps'], null);
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
	protected function addScripts(): void
	{
		$this->scripts->each(static function ($script) {
			$load = Data::value(Arr::value($script, 'load'));

			if ($load) {
				if (is_string($load)) {
					wp_deregister_script($script['id']);
					wp_enqueue_script($script['id'], Twist::asset()
					                                      ->url($load, $script['parent']), $script['deps'], null, true);
				} else {
					wp_enqueue_script($script['id']);
				}
			} else {
				wp_dequeue_script($script['id']);
			}
		});
	}

	/**
	 * Adds inline <script> in the footer.
	 */
	protected function addInlineScripts(): void
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
	 * @param array $links
	 *
	 * @return array
	 */
	protected function addLinks(array $links): array
	{
		foreach ($this->links as $attributes) {
			ksort($attributes);

			$links[] = Tag::link($attributes);
		}

		return $links;
	}

	/**
	 * @param array $metas
	 *
	 * @return array
	 */
	protected function addMetas(array $metas): array
	{
		foreach ($this->metas as $attributes) {
			krsort($attributes);

			$metas[] = Tag::meta($attributes);
		}

		return $metas;
	}

	/**
	 * Adds extra HTML attributes to script elements.
	 *
	 * @param string $script
	 * @param string $handle
	 *
	 * @return string
	 */
	protected function addScriptsAttributes(string $script, string $handle): string
	{
		$scripts = $this->scripts->filter(static function ($script) {
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
	protected function addResourceHints(array $urls, string $relation): array
	{
		if (array_key_exists($relation, $this->resources)) {
			$urls = Arr::merge($urls, $this->resources[$relation]);
		}

		return $urls;
	}

	/**
	 * Register sidebars.
	 */
	protected function addSidebars(): void
	{
		$this->sidebars->each(static function ($sidebar) {
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
	protected function addContactMethods(array $methods): array
	{
		return array_merge($methods, $this->contact);
	}

	/**
	 * @param Collection $collection
	 * @param array      $assets
	 *
	 * @return Collection
	 */
	protected function addAssets(Collection $collection, array $assets): Collection
	{
		return $this->addToCollection($collection, array_map(function (array $asset) {
			if (isset($asset['load']) && $asset['load'] !== false) {
				if (!isset($asset['parent'])) {
					$asset['parent'] = $this->parent;
				}

				if (!isset($asset['deps'])) {
					$asset['deps'] = [];
				}
			}

			return $asset;
		}, $assets));
	}

	/**
	 * @param Collection $collection
	 * @param array      $array
	 *
	 * @return Collection
	 */
	protected function addToCollection(Collection $collection, array $array): Collection
	{
		return Collection::make($array)->merge($collection)->unique('id');
	}

}