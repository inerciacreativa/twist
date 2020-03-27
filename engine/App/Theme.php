<?php

namespace Twist\App;

use Closure;
use Twist\Library\Data\Collection;
use Twist\Library\Hook\Hookable;
use Twist\Library\Support\Arr;
use Twist\Twist;
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
	 * @var Closure[]
	 */
	private $setup = [];

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @var Collection
	 */
	private $sidebars;

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
	 */
	public function __construct(App $app, Config $config)
	{
		$this->app      = $app;
		$this->config   = $config;
		$this->sidebars = new Collection();

		$this->hook()
			 ->before(Action::BOOT, 'boot')
			 ->on('show_admin_bar', '__return_false')
			 ->on('user_contactmethods', 'addContactMethods')
			 ->on('widgets_init', 'addSidebars');
	}

	/**
	 * @param Closure $setup
	 */
	public function child(Closure $setup): void
	{
		$this->setup[self::CHILD] = $setup;
	}

	/**
	 * @param Closure $setup
	 */
	public function parent(Closure $setup): void
	{
		$this->setup[self::PARENT] = $setup;
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
		$this->setConfig();
		$this->loadLanguages();
		$this->addThemeSupport();

		$this->app->boot();
	}

	/**
	 * Adds config options.
	 */
	protected function setConfig(): void
	{
		$this->config->set($this->getDefaultConfig());

		$this->setup[self::PARENT]();
		if (isset($this->setup[self::CHILD])) {
			$this->setup[self::CHILD]();
		}

		$this->config->set($this->options);
	}

	/**
	 * @return array
	 */
	protected function getDefaultConfig(): array
	{
		return [
			'dir'  => [
				'home'       => defined('WP_ROOT') ? WP_ROOT : ABSPATH,
				'stylesheet' => get_stylesheet_directory(),
				'template'   => get_template_directory(),
				'upload'     => wp_upload_dir(null, false)['basedir'],
			],
			'uri'  => [
				'home'       => home_url(),
				'stylesheet' => get_stylesheet_directory_uri(),
				'template'   => get_template_directory_uri(),
			],
			'view' => [
				'debug'     => Twist::isDebug(),
				'service'   => TwigViewService::id(),
				'namespace' => TwigViewService::MAIN_NAMESPACE,
				'folder'    => '/templates',
				'context'   => [],
			],
		];
	}

	/**
	 * Load translations.
	 */
	protected function loadLanguages(): void
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
