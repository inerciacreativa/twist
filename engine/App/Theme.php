<?php

namespace Twist\App;

use Twist\Library\Data\Collection;
use Twist\Library\Hook\HookDecorator;
use Twist\Library\Util\Arr;
use Twist\Library\Util\Data;
use Twist\Library\Util\Tag;
use Twist\Service\ServiceProviderInterface;
use Twist\View\Twig\TwigService;

/**
 * Class Theme
 *
 * @package Twist\App
 */
class Theme
{

	use HookDecorator;

	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var Asset
	 */
	protected $asset;

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
	protected $formats = [
		'aside',
		'image',
		'video',
		'quote',
		'link',
		'gallery',
		'audio',
	];

	/**
	 * Theme constructor.
	 *
	 * @param App    $app
	 * @param Config $config
	 * @param Asset  $asset
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Pimple\Exception\FrozenServiceException
	 */
	public function __construct(App $app, Config $config, Asset $asset)
	{
		$this->app    = $app;
		$this->config = $config;
		$this->asset  = $asset;

		$this->styles   = new Collection();
		$this->scripts  = new Collection();
		$this->sidebars = new Collection();

		$this->hook()
		     ->before('after_setup_theme', 'boot')
		     ->on('show_admin_bar', '__return_false')
		     ->on('get_the_generator_html', '__return_empty_string')
		     ->on('get_the_generator_xhtml', '__return_empty_string')
		     ->on('get_the_generator_rss2', '__return_empty_string')
		     ->on('user_contactmethods', 'addContactMethods')
		     ->on('wp_enqueue_scripts', 'addStyles')
		     ->on('wp_enqueue_scripts', 'addScripts')
		     ->on('widgets_init', 'addSidebars')
		     ->after('script_loader_tag', 'addScriptsAttributes', ['arguments' => 2])
		     ->after('wp_resource_hints', 'addResourceHints', ['arguments' => 2])
		     ->after('wp_footer', 'addWebFonts')
		     ->on('twist_site_links', function (array $links) {
			     return array_filter($links, function (Tag $link) {
				     return !\in_array($link['rel'], [
					     'EditURI',
					     'wlwmanifest',
				     ], false);
			     });
		     });
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
		$this->options = Arr::merge($this->options, $config);

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
	 * @param array $formats
	 *
	 * @return $this
	 */
	public function formats(array $formats): self
	{
		$this->formats = $formats;

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
		$this->addServices();
		$this->addThemeSupport();

		$this->app->boot();
	}

	/**
	 * Adds config options.
	 */
	protected function addConfig(): void
	{
		$this->config->fill([
			'dir'  => [
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
				'service'   => TwigService::id(),
				'debug'     => \defined('WP_DEBUG') && WP_DEBUG,
				'templates' => '/templates',
			],
		]);

		$this->hook()->fire('twist_theme', $this);

		$this->config->fill($this->options);

		$this->config->fill([
			'view' => [
				'cache' => $this->config->get('view.debug') ? false : $this->config->get('dir.upload') . '/view_cache',
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
	 * Registers theme support for several features.
	 */
	protected function addThemeSupport(): void
	{
		add_theme_support('customize-selective-refresh-widgets');
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
	 * Enqueue styles.
	 */
	protected function addStyles(): void
	{
		$this->styles->each(function ($style) {
			$load = Data::value(Arr::value($style, 'load'));

			if ($load) {
				if (\is_string($load)) {
					wp_enqueue_style($style['id'], $this->asset->url($load, Arr::value($style, 'parent', false)), Arr::value($style, 'deps'), null);
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
		$this->scripts->each(function ($script) {
			$load = Data::value(Arr::value($script, 'load'));

			if ($load) {
				if (\is_string($load)) {
					wp_deregister_script($script['id']);
					wp_enqueue_script($script['id'], $this->asset->url($load, Arr::value($script, 'parent', false)), Arr::value($script, 'deps'), null, true);
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
	protected function addScriptsAttributes(string $script, string $handle): string
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
	protected function addResourceHints(array $urls, string $relation): array
	{
		if (array_key_exists($relation, $this->resources)) {
			$urls = array_merge($urls, $this->resources[$relation]);
		}

		return $urls;
	}

	/**
	 * Register sidebars.
	 */
	protected function addSidebars(): void
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
	protected function addContactMethods(array $methods): array
	{
		return array_merge($methods, $this->contact);
	}

	/**
	 * Adds web fonts using Web Font Loader.
	 *
	 * @see https://github.com/typekit/webfontloader
	 */
	protected function addWebFonts(): void
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
	      const wf = d.createElement('script'), s = d.scripts[0];
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
		return Collection::make($array)->merge($collection)->unique('id');
	}

}