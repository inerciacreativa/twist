<?php /** @noinspection NullPointerExceptionInspection */

namespace Twist\Model\Site;

use Twist\App\AppException;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Classes;
use Twist\Library\Support\Macroable;
use Twist\Model\Link\Links;
use Twist\Model\Navigation\Navigation;
use Twist\Model\Post\Post;
use Twist\Model\Post\PostsQuery;
use Twist\Model\Site\Assets\AssetsGroup;
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
	 * @return AssetsGroup
	 */
	public function head(): AssetsGroup
	{
		return Assets::head();
	}

	/**
	 * @return AssetsGroup
	 */
	public function foot(): AssetsGroup
	{
		return Assets::foot();
	}

	/**
	 * @param int|string|array $menu
	 *
	 * @return Links
	 */
	public function navigation($menu): Links
	{
		return Navigation::make($menu);
	}

	/**
	 * @return string
	 */
	public static function title(): string
	{
		static $title;

		if (!isset($title)) {
			Hook::add('document_title_separator', static function () {
				return '–';
			});

			$title = wp_get_document_title();
		}

		return $title;
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
	public static function description(): string
	{
		return get_bloginfo('description');
	}

	/**
	 * @return string
	 */
	public static function search(): string
	{
		return get_search_query();
	}

	/**
	 * @return string
	 */
	public static function current_url(): string
	{
		global $wp;

		return self::home_url(trailingslashit($wp->request));
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

		if (PostsQuery::main()->is_front_page()) {
			$classes->add('home');
		}

		if (PostsQuery::main()->is_home()) {
			$classes->add('blog');
		}

		if (PostsQuery::main()->is_archive()) {
			$classes->add('archive');
		}

		if (PostsQuery::main()->is_date()) {
			$classes->add('date');
		}

		if (PostsQuery::main()->is_search()) {
			$classes->add('search');
			$classes->add(PostsQuery::main()
									->total() > 0 ? 'search-has-results' : 'search-has-no-results');
		}

		if (PostsQuery::main()->is_404()) {
			$classes->add('not-found');
		}

		if (PostsQuery::main()->is_paged()) {
			$classes->add('paged');
		}

		if (PostsQuery::main()->is_singular()) {
			/** @var Post $post */
			$post = PostsQuery::main()->posts()->first();

			if (PostsQuery::main()->is_single()) {
				$classes->add('single single-' . Classes::sanitize($post->type(), $post->id()));

				if ($post->has_format()) {
					$classes->add($post->format('single-format'));
				}
			}

			if (PostsQuery::main()->is_attachment()) {
				$classes->add('attachment attachment-' . $post->mime_type());
			}

			if (PostsQuery::main()->is_page()) {
				$classes->add('page page-' . Classes::sanitize($post->name()));
				if ($post->has_parent()) {
					$classes->add('page-has-parent');
				}
				if ($post->has_children()) {
					$classes->add('page-has-children');
				}
			}
		} else if (PostsQuery::main()->is_archive()) {
			if (PostsQuery::main()->is_post_type_archive()) {
				$type = PostsQuery::main()->get('post_type');
				if (is_array($type)) {
					$type = reset($type);
				}

				$classes->add('archive-' . Classes::sanitize($type));
			} else if (PostsQuery::main()->is_author()) {
				$author = new User(PostsQuery::main()->queried_object());

				$classes->add('archive-author author-' . Classes::sanitize($author->nice_name(), $author->id()));
			} else if (PostsQuery::main()->is_category()) {
				$term = new Term(PostsQuery::main()->queried_object());

				$classes->add('archive-category category-' . Classes::sanitize($term->slug(), $term->id()));
			} else if (PostsQuery::main()->is_tag()) {
				$term = new Term(PostsQuery::main()->queried_object());

				$classes->add('archive-tag tag-' . Classes::sanitize($term->slug(), $term->id()));
			} else if (PostsQuery::main()->is_taxonomy()) {
				$term     = new Term(PostsQuery::main()->queried_object());
				$taxonomy = Classes::sanitize($term->taxonomy());

				$classes->add("$taxonomy $taxonomy-" . Classes::sanitize($term->slug(), $term->id()));
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

}
