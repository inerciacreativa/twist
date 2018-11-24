<?php

namespace Twist\Model\Site;

use Twist\Library\Hook\Hook;
use Twist\Library\Util\Macro;
use Twist\Library\Util\Tag;
use Twist\Model\Navigation\Links;
use Twist\Model\Navigation\Navigation;
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
	 * @var Navigation
	 */
	protected $navigation;

	/**
	 * @var Pagination
	 */
	protected $pagination;

	/**
	 * @var Asset
	 */
	protected $assets;

	/**
	 * Site constructor.
	 */
	public function __construct()
	{
		$this->assets     = new Asset($this);
		$this->navigation = new Navigation();
	}

	/**
	 * @return Head
	 */
	public function head(): Head
	{
		return new Head();
	}

	/**
	 * @return Foot
	 */
	public function foot(): Foot
	{
		return new Foot();
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
	 * @param string $path
	 *
	 * @return string
	 */
	public function home_url(string $path = '/'): string
	{
		return home_url($path);
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public function site_url(string $path = '/'): string
	{
		return site_url($path);
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public function admin_url(string $path = '/'): string
	{
		return admin_url($path);
	}

	/**
	 * @return Asset
	 */
	public function assets(): Asset
	{
		return $this->assets;
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
		$url = parse_url($this->home_url(), PHP_URL_HOST);
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
			$post      = $wp_query->get_queried_object();
			$post_id   = $post->ID;
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
		$classes = implode(' ', Hook::apply('body_class', $classes, $options['classes']));

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
	public function widgets(string $sidebar): void
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
	 * @return bool
	 */
	public function has_pagination(): bool
	{
		return $this->pagination()->has_pages();
	}

	/**
	 * @return Pagination
	 */
	public function pagination(): Pagination
	{
		if ($this->pagination === null) {
			$this->pagination = new Pagination();
		}

		return $this->pagination;
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

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function option(string $name)
	{
		return get_option($name);
	}

	/**
	 * @param string $tag
	 * @param array  $attributes
	 * @param null   $content
	 *
	 * @return Tag
	 */
	public function tag(string $tag, array $attributes = [], $content = null): Tag
	{
		return Tag::make($tag, $attributes, $content);
	}

}