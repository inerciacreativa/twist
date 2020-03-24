<?php

namespace Twist\App;

use Closure;
use Twist\Asset\Fonts;
use Twist\Asset\Queue;
use Twist\Library\Data\Collection;
use Twist\Library\Hook\Hookable;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Arr;
use Twist\Service\ServiceProviderInterface;
use Twist\View\Twig\TwigViewService;

/**
 * Class Theme
 *
 * @package Twist\App
 */
class Theme
{

	use Hookable;

	public const PARENT = 'template';

	public const CHILD = 'stylesheet';

	/**
	 * @var App
	 */
	private $app;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var Queue
	 */
	private $queue;

	/**
	 * @var Fonts
	 */
	private $fonts;

	/**
	 * @var bool
	 */
	private $parent = true;

	/**
	 * @var Closure
	 */
	private $setup;

	/**
	 * @var array
	 */
	private $services = [];

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @var array
	 */
	private $links = [];

	/**
	 * @var array
	 */
	private $metas = [];

	/**
	 * @var Collection
	 */
	private $sidebars;

	/**
	 * @var array
	 */
	private $resources = [];

	/**
	 * @var array
	 */
	private $logo = [];

	/**
	 * @var array
	 */
	private $images = [];

	/**
	 * @var array
	 */
	private $menus = [];

	/**
	 * @var array
	 */
	private $contact = [];

	/**
	 * @var array
	 */
	private $formats = [];

	/**
	 * Theme constructor.
	 *
	 * @param App    $app
	 * @param Config $config
	 * @param Queue  $queue
	 */
	public function __construct(App $app, Config $config, Queue $queue, Fonts $fonts)
	{
		$this->app    = $app;
		$this->config = $config;
		$this->queue  = $queue;
		$this->fonts  = $fonts;

		$this->sidebars = new Collection();

		$this->hook()
			 ->before(App::BOOT, 'boot')
			 ->on('show_admin_bar', '__return_false')
			 ->on('user_contactmethods', 'addContactMethods')
			 ->on('twist_site_links', 'addLinks')
			 ->on('twist_site_metas', 'addMetas')
			 ->on('widgets_init', 'addSidebars')
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

	/**
	 * @param string $path
	 * @param string $manifest
	 *
	 * @return $this
	 */
	public function assets(string $path, string $manifest): self
	{
		return $this->options([
			'asset' => [
				$this->parent ? self::PARENT : self::CHILD => [
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
		$this->queue->styles($styles, $this->parent);

		return $this;
	}

	/**
	 * @param array $scripts
	 *
	 * @return $this
	 */
	public function scripts(array $scripts): self
	{
		$this->queue->scripts($scripts, $this->parent);

		return $this;
	}

	/**
	 * @param string|callable $script
	 *
	 * @return $this
	 */
	public function inline($script): self
	{
		$this->queue->inline($script);

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
	 * @param array       $fonts
	 * @param string|bool $loader
	 *
	 * @return $this
	 */
	public function webfonts(array $fonts, $loader = true): self
	{
		$this->fonts->add($fonts, $loader);

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
	 * [
	 *   'height'      (int)
	 *   'width'       (int)
	 *   'flex-width'  (bool)
	 *   'flex-height' (bool)
	 *   'header-text' (string)
	 * ]
	 *
	 * @param array $logo
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
	 * @param array $add
	 * @param array $remove
	 *
	 * @return $this
	 * @see filter 'user_contactmethods'
	 *
	 */
	public function contact(array $add, array $remove = []): self
	{
		$this->contact = compact('add', 'remove');

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
	 * Start application.
	 */
	protected function boot(): void
	{
		$this->addConfig();
		$this->addLanguages();
		$this->addThemeSupport();
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
				'service'   => TwigViewService::id(),
				'namespace' => TwigViewService::MAIN_NAMESPACE,
				'folder'    => '/templates',
				'context'   => [],
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
				'paths' => $this->getViewPaths($this->config->get('dir.stylesheet'), $this->config->get('dir.template'), $this->config->get('view.folder'), $this->config->get('view.namespace')),
			],
		]);
	}

	/**
	 * @param string      $stylesheet
	 * @param string      $template
	 * @param string      $folder
	 * @param string|null $namespace
	 *
	 * @return array
	 */
	protected function getViewPaths(string $stylesheet, string $template, string $folder, string $namespace = null): array
	{
		$paths = [];

		if ($namespace) {
			$paths[] = [$namespace, $stylesheet];
		}

		$paths[] = [basename($stylesheet), $stylesheet];

		if ($template !== $stylesheet) {
			$paths[] = [basename($template), $template];
		}

		return array_map(static function (array $path) use ($folder) {
			return [
				'namespace' => $path[0],
				'path'      => $path[1] . $folder,
			];
		}, $paths);
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
		$methods = array_merge($methods, $this->contact['add']);
		$methods = Arr::remove($methods, $this->contact['remove'], false);

		return $methods;
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
