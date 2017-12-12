<?php

namespace Twist\Model\Site;

use function Twist\asset_url;
use Twist\Library\Util\Macro;
use Twist\Library\Util\Tag;
use Twist\Model\Navigation\Navigation;
use Twist\Model\Navigation\Links;
use Twist\Model\Navigation\Pagination;

/**
 * Class Site
 *
 * @package Twist\Model\Site
 */
class Site
{

	use Macro;

	/**
	 * @var Header
	 */
	protected $header;

	/**
	 * @var Footer
	 */
	protected $footer;

	/**
	 * @var Navigation
	 */
	protected $navigation;

	/**
	 * Site constructor.
	 */
	public function __construct()
	{
		$this->navigation = new Navigation();
	}

	/**
	 * @return Header
	 */
	public function head(): Header
	{
		if ($this->header === null) {
			$this->header = new Header();
		}

		return $this->header;
	}

	/**
	 * @return string
	 */
	public function charset(): string
	{
		return get_bloginfo('charset');
	}

	/**
	 * @return string
	 */
	public function language(): string
	{
		return get_bloginfo('language');
	}

	/**
	 * @return string
	 */
	public function url(): string
	{
		return home_url('/');
	}

	/**
	 * @param string $source
	 * @param int    $width
	 * @param int    $height
	 *
	 * @return string
	 */
	public function logo(string $source = null, int $width = 0, int $height = 0): string
	{
		if (empty($source) && ($id = get_theme_mod('custom_logo'))) {
			$image = Tag::parse(wp_get_attachment_image($id, 'full'));
		} else {
			$image = Tag::img([
				'src'    => asset_url($source),
				'width'  => $width,
				'height' => $height,
			]);
		}

		$image->attributes([
			'alt'      => $this->name(),
			'class'    => '',
			'itemprop' => 'contentUrl',
		]);

		/*
		return Tag::a([
			'href'     => $this->url(),
			'class'    => 'site-link',
			'rel'      => 'home',
			'itemprop' => 'url',
			'id'       => 'site-logo',
		], Tag::span([
			'itemprop'  => 'logo',
			'itemscope' => true,
			'itemtype'  => 'http://schema.org/ImageObject',
		], $image));
		*/

		return $image;
	}

	/**
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return string
	 */
	public function asset(string $filename, bool $parent = false): string
	{
		return asset_url($filename, $parent);
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return get_bloginfo('name');
	}

	/**
	 * @return string
	 */
	public function id(): string
	{
		$url = parse_url($this->url(), PHP_URL_HOST);
		$url = str_replace('.', '-', $url);

		return $url;
	}

	/**
	 * @see body_class()
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function classes(array $options = []): string
	{
		/**
		 * @var $wp_query \WP_Query
		 */
		global $wp_query;

		$options = wp_parse_args($options, [
			'classes' => '',
			'front'   => 'home',
			'index'   => 'blog',
		]);

		$classes = [];

		if (is_front_page()) {
			$classes[] = $options['front'];
		}
		if (is_home()) {
			$classes[] = $options['index'];
		}
		if (is_archive()) {
			$classes[] = 'archive';
		}
		if (is_date()) {
			$classes[] = 'date';
		}
		if (is_search()) {
			$classes[] = 'search';
			$classes[] = $wp_query->posts ? 'search-results' : 'search-no-results';
		}
		if (is_attachment()) {
			$classes[] = 'attachment';
		}
		if (is_404()) {
			$classes[] = 'not-found';
		}

		if (is_singular()) {
			$post_id   = $wp_query->get_queried_object_id();
			$post      = $wp_query->get_queried_object();
			$post_type = $post->post_type;

			if (is_page_template()) {
				$classes[] = "{$post_type}-template";

				$template_slug  = get_page_template_slug($post_id);
				$template_parts = explode('/', $template_slug);

				foreach ($template_parts as $part) {
					$classes[] = "{$post_type}-template-" . sanitize_html_class(str_replace([
							'.',
							'/',
						], '-', basename($part, '.php')));
				}

				$classes[] = "{$post_type}-template-" . sanitize_html_class(str_replace('.', '-', $template_slug));
			}

			if (is_single()) {
				$classes[] = 'single';
				if (isset($post->post_type)) {
					$classes[] = 'single-' . sanitize_html_class($post->post_type, $post_id);

					// Post Format
					if ($post_format = get_post_format($post->ID)) {
						$classes[] = 'format-' . sanitize_html_class($post_format);
					}
				}
			}

			if (is_page()) {
				$classes[] = 'page';
			}
		} else if (is_archive()) {
			if (is_post_type_archive()) {
				$post_type = get_query_var('post_type');
				if (\is_array($post_type)) {
					$post_type = reset($post_type);
				}
				$classes[] = 'archive-' . sanitize_html_class($post_type);
			} else if (is_author()) {
				$classes[] = 'archive-author';
			} else if (is_category()) {
				$classes[] = 'archive-category';
			} else if (is_tag()) {
				$classes[] = 'archive-tag';
			} else if (is_tax()) {
				$term      = $wp_query->get_queried_object();
				$classes[] = 'archive-' . sanitize_html_class($term->taxonomy);
			}
		}

		if (is_user_logged_in()) {
			$classes[] = 'user-logged-in';
		}

		if (is_admin_bar_showing()) {
			$classes[] = 'admin-bar';
		}

		if (!empty($options->class)) {
			if (!\is_array($options->class)) {
				$options->class = preg_split('#\s+#', $options->class);
			}

			$classes = array_merge($classes, $options->class);
		}

		$classes = array_filter($classes);
		$classes = implode(' ', apply_filters('body_class', $classes, $options['classes']));

		return $classes;
	}

	/**
	 * @param string $sidebar
	 *
	 * @return bool
	 */
	public function has_widgets(string $sidebar): bool
	{
		return is_active_sidebar($sidebar);
	}

	/**
	 * @param string $sidebar
	 */
	public function widgets(string $sidebar)
	{
		if (is_active_sidebar($sidebar)) {
			dynamic_sidebar($sidebar);
		}
	}

	/**
	 * @return string
	 */
	public function search(): string
	{
		return get_search_query();
	}

	/**
	 * @param string $menu
	 * @param string $location
	 * @param int    $depth
	 *
	 * @return Links
	 */
	public function navigation(string $menu, $location = null, $depth = 0): Links
	{
		return $this->navigation->get($menu, $location, $depth);
	}

	/**
	 * @return Pagination
	 */
	public function pagination(): Pagination
	{
		return new Pagination();
	}

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 */
	public function pager(array $arguments = []): Links
	{
		return $this->pagination()->get($arguments);
	}

	/**
	 * @return Footer
	 */
	public function foot(): Footer
	{
		if ($this->footer === null) {
			$this->footer = new Footer();
		}

		return $this->footer;
	}

	/**
	 * @param string $format
	 *
	 * @return string
	 */
	public function date(string $format): string
	{
		return date($format);
	}

}