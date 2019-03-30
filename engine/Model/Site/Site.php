<?php /** @noinspection NullPointerExceptionInspection */

namespace Twist\Model\Site;

use Twist\App\AppException;
use Twist\Library\Html\Classes;
use Twist\Library\Util\Macroable;
use Twist\Model\Navigation\Links;
use Twist\Model\Navigation\Navigation;
use Twist\Model\Navigation\Pagination;
use Twist\Model\Post\Post;
use Twist\Model\Post\Query;
use Twist\Model\Taxonomy\Term;
use Twist\Model\User\User;

/**
 * Class Site
 *
 * @package Twist\Model\Site
 */
class Site
{

	use Macroable;

	/**
	 * @var Head
	 */
	private $head;

	/**
	 * @var Foot
	 */
	private $foot;

	/**
	 * @var Navigation
	 */
	private $navigation;

	/**
	 * @var Pagination
	 */
	private $pagination;

	/**
	 * @var Assets
	 */
	private $assets;

	/**
	 * @return Head
	 */
	public function head(): Head
	{
		return $this->head ?? $this->head = new Head();
	}

	/**
	 * @return Foot
	 */
	public function foot(): Foot
	{
		return $this->foot ?? $this->foot = new Foot();
	}

	/**
	 * @return Assets
	 */
	public function assets(): Assets
	{
		return $this->assets ?? $this->assets = new Assets();
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
		if ($this->navigation === null) {
			$this->navigation = new Navigation();
		}

		return $this->navigation->get($menu, $location, $depth);
	}

	/**
	 * @return bool
	 * @throws AppException
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
		return $this->pagination ?? $this->pagination = new Pagination();
	}

	/**
	 * @return string
	 */
	public static function id(): string
	{
		return str_replace('.', '-', parse_url(self::home_url(), PHP_URL_HOST));
	}

	/**
	 * @return string
	 */
	public static function name(): string
	{
		return get_bloginfo('name');
	}

	/**
	 * @return string
	 */
	public static function charset(): string
	{
		return get_bloginfo('charset');
	}

	/**
	 * @return string
	 */
	public static function language(): string
	{
		return get_bloginfo('language');
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function home_url(string $path = '/'): string
	{
		return home_url($path);
	}

	/**
	 * @param string      $path
	 * @param string|null $scheme
	 *
	 * @return string
	 */
	public static function site_url(string $path = '/', string $scheme = null): string
	{
		return site_url($path, $scheme);
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function admin_url(string $path = '/'): string
	{
		return admin_url($path);
	}

	/**
	 * @param string|array $class
	 *
	 * @return Classes
	 * @throws AppException
	 * @see body_class()
	 *
	 */
	public function classes($class = []): Classes
	{
		$classes = Classes::make($class);

		if (Query::main()->is_front_page()) {
			$classes->add('home');
		}

		if (Query::main()->is_home()) {
			$classes->add('blog');
		}

		if (Query::main()->is_archive()) {
			$classes->add('archive');
		}

		if (Query::main()->is_date()) {
			$classes->add('date');
		}

		if (Query::main()->is_search()) {
			$classes->add('search');
			$classes->add(Query::main()
			                   ->total() > 0 ? 'search-has-results' : 'search-has-no-results');
		}

		if (Query::main()->is_404()) {
			$classes->add('not-found');
		}

		if (Query::main()->is_paged()) {
			$classes->add('paged');
		}

		if (Query::main()->is_singular()) {
			/** @var Post $post */
			$post = Query::main()->posts()->first();

			if (Query::main()->is_single()) {
				$classes->add('single single-' . $classes->sanitize($post->type(), $post->id()));

				if ($post->has_format()) {
					$classes->add($post->format('single-format'));
				}
			}

			if (Query::main()->is_attachment()) {
				$classes->add('attachment attachment-' . $post->mime_type());
			}

			if (Query::main()->is_page()) {
				$classes->add('page page-' . $classes->sanitize($post->name()));
				if ($post->has_parent()) {
					$classes->add('page-has-parent');
				}
				if ($post->has_children()) {
					$classes->add('page-has-children');
				}
			}
		} else if (Query::main()->is_archive()) {
			if (Query::main()->is_post_type_archive()) {
				$type = Query::main()->get('post_type');
				if (is_array($type)) {
					$type = reset($type);
				}

				$classes->add('archive-' . $classes->sanitize($type));
			} else if (Query::main()->is_author()) {
				$author = new User(Query::main()->queried_object());

				$classes->add('archive-author author-' . $classes->sanitize($author->nice_name(), $author->id()));
			} else if (Query::main()->is_category()) {
				$term = new Term(Query::main()->queried_object());

				$classes->add('archive-category category-' . $classes->sanitize($term->slug(), $term->id()));
			} else if (Query::main()->is_tag()) {
				$term = new Term(Query::main()->queried_object());

				$classes->add('archive-tag tag-' . $classes->sanitize($term->slug(), $term->id()));
			} else if (Query::main()->is_taxonomy()) {
				$term     = new Term(Query::main()->queried_object());
				$taxonomy = $classes->sanitize($term->taxonomy());

				$classes->add("$taxonomy $taxonomy-" . $classes->sanitize($term->slug(), $term->id()));
			}
		}

		if (User::current()->is_logged()) {
			$classes->add('user-logged-in');
		}

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

}