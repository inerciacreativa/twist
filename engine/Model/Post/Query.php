<?php

namespace Twist\Model\Post;

use Twist\App\Action;
use Twist\App\AppException;
use Twist\Library\Hook\Hook;
use Twist\Library\Support\Arr;
use Twist\Model\IterableInterface;
use WP_Query;

/**
 * Class Query
 *
 * @package Twist\Model\Post
 */
class Query implements IterableInterface
{

	/**
	 * @var Query[]
	 */
	static private $queries = [];

	/**
	 * @var WP_Query
	 */
	private $query;

	/**
	 * @param array $query
	 *
	 * @return Query
	 */
	private static function query(array $query = []): Query
	{
		$id = json_encode($query);

		if (!array_key_exists($id, static::$queries)) {
			static::$queries[$id] = new static($query);
		}

		return static::$queries[$id];
	}

	/**
	 * @return Query
	 *
	 * @throws AppException
	 */
	public static function main(): Query
	{
		if (!Hook::fired(Action::QUERY)) {
			new AppException('The main query has not been parsed yet.');
		}

		return static::query();
	}

	/**
	 * @param array $query
	 * @param bool  $defaults
	 *
	 * @return Query
	 */
	public static function make(array $query, bool $defaults = true): Query
	{
		if ($defaults) {
			$query = array_merge($query, [
				'suppress_filters'    => true,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			]);
		}

		$query = array_merge([
			'post_type'      => 'post',
			'posts_per_page' => get_option('posts_per_page'),
		], $query);

		if (empty($query['post_status'])) {
			$query['post_status'] = ($query['post_type'] === 'attachment') ? 'inherit' : 'publish';
		}

		if (!empty($query['include'])) {
			$ids = wp_parse_id_list($query['include']);

			$query['posts_per_page'] = count($ids);
			$query['post__in']       = $ids;
		} else if (!empty($query['exclude'])) {
			$query['post__not_in'] = wp_parse_id_list($query['exclude']);
		}

		return static::query($query);
	}

	/**
	 * @param int   $number
	 * @param array $query
	 *
	 * @return Query
	 */
	public static function latest(int $number = 5, array $query = []): Query
	{
		$parameters = array_merge([
			'orderby' => 'post_date',
			'order'   => 'DESC',
		], $query, [
			'posts_per_page' => $number,
		]);

		return self::make($parameters);
	}

	/**
	 * @param string $search
	 * @param array  $query
	 *
	 * @return Query
	 */
	public static function search(string $search = '', array $query = []): Query
	{
		global $wp;

		if (empty($search)) {
			$request = explode('/', $wp->request);
			$search  = str_replace('-', ' ', end($request));
			$search  = (string) preg_replace('/[^a-z ]/i', '', $search);
		}

		$parameters = array_merge($query, [
			's' => $search,
		]);

		return self::make($parameters);
	}

	/**
	 * PostQuery constructor.
	 *
	 * @param array $query
	 */
	public function __construct(array $query = [])
	{
		global $wp_query;

		$this->query = $query ? new WP_Query($query) : $wp_query;
	}

	/**
	 * @return mixed
	 */
	public function queried_object()
	{
		return $this->query->get_queried_object();
	}

	/**
	 * @return int
	 */
	public function queried_id(): int
	{
		return (int) $this->query->get_queried_object_id();
	}

	/**
	 * @return WP_Query
	 */
	public function object(): WP_Query
	{
		return $this->query;
	}

	/**
	 * @return int[]
	 */
	public function ids(): array
	{
		if ($this->count()) {
			return Arr::pluck($this->query->posts, 'ID');
		}

		return [];
	}

	/**
	 * @return Posts
	 */
	public function posts(): Posts
	{
		return Posts::make($this->query->posts);
	}

	/**
	 * @inheritdoc
	 */
	public function count(): int
	{
		return $this->query->post_count;
	}

	/**
	 * @return int
	 */
	public function total(): int
	{
		return $this->query->found_posts;
	}

	/**
	 * @param string $variable
	 * @param null   $default
	 *
	 * @return mixed
	 */
	public function get(string $variable, $default = null)
	{
		return $this->query->get($variable, $default);
	}

	/**
	 * @param string $variable
	 * @param        $value
	 */
	public function set(string $variable, $value): void
	{
		$this->query->set($variable, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function rewind(): void
	{
		$this->query->rewind_posts();
	}

	/**
	 * @inheritdoc
	 */
	public function next(): void
	{
	}

	/**
	 * @inheritdoc
	 */
	public function valid(): bool
	{
		return $this->query->have_posts();
	}

	/**
	 * @return Post
	 * @throws AppException
	 */
	public function current(): Post
	{
		$this->query->the_post();

		return new Post();
	}

	/**
	 * @inheritdoc
	 */
	public function key(): int
	{
		return $this->query->post->ID;
	}

	/**
	 * @return bool
	 */
	public function is_paginated(): bool
	{
		return $this->query->max_num_pages > 1;
	}

	/**
	 * @return bool
	 */
	public function is_main(): bool
	{
		return $this->query->is_main_query();
	}

	/**
	 * @return bool
	 */
	public function is_feed(): bool
	{
		return $this->query->is_feed();
	}

	/**
	 * @return bool
	 */
	public function is_home(): bool
	{
		return $this->query->is_home();
	}

	/**
	 * @return bool
	 */
	public function is_front_page(): bool
	{
		return $this->query->is_front_page();
	}

	/**
	 * @param null|int|string|array $page
	 *
	 * @return bool
	 */
	public function is_page($page = null): bool
	{
		return $this->query->is_page($page);
	}

	/**
	 * @param null|int|string|array $post
	 *
	 * @return bool
	 */
	public function is_single($post = null): bool
	{
		return $this->query->is_single($post);
	}

	/**
	 * @param null|string|array $post_type
	 *
	 * @return bool
	 */
	public function is_singular($post_type = null): bool
	{
		return $this->query->is_singular($post_type);
	}

	/**
	 * @param null|int|string|array $attachment
	 *
	 * @return bool
	 */
	public function is_attachment($attachment = null): bool
	{
		return $this->query->is_attachment($attachment);
	}

	/**
	 * @return bool
	 */
	public function is_archive(): bool
	{
		return $this->query->is_archive();
	}

	/**
	 * @param null|string|array $post_type
	 *
	 * @return bool
	 */
	public function is_post_type_archive($post_type = null): bool
	{
		return $this->query->is_post_type_archive($post_type);
	}

	/**
	 * @param null|int|string|array $category
	 *
	 * @return bool
	 */
	public function is_author($category = null): bool
	{
		return $this->query->is_author($category);
	}

	/**
	 * @param null|int|string|array $category
	 *
	 * @return bool
	 */
	public function is_category($category = null): bool
	{
		return $this->query->is_category($category);
	}

	/**
	 * @param null|int|string|array $tag
	 *
	 * @return bool
	 */
	public function is_tag($tag = null): bool
	{
		return $this->query->is_tag($tag);
	}

	/**
	 * @param null|int|string|array $taxonomy
	 * @param null|int|string|array $term
	 *
	 * @return bool
	 */
	public function is_taxonomy($taxonomy = null, $term = null): bool
	{
		return $this->query->is_tax($taxonomy, $term);
	}

	/**
	 * @return bool
	 */
	public function is_date(): bool
	{
		return $this->query->is_date();
	}

	/**
	 * @return bool
	 */
	public function is_time(): bool
	{
		return $this->query->is_time();
	}

	/**
	 * @return bool
	 */
	public function is_day(): bool
	{
		return $this->query->is_day();
	}

	/**
	 * @return bool
	 */
	public function is_month(): bool
	{
		return $this->query->is_month();
	}

	/**
	 * @return bool
	 */
	public function is_year(): bool
	{
		return $this->query->is_year();
	}

	/**
	 * @return bool
	 */
	public function is_search(): bool
	{
		return $this->query->is_search();
	}

	/**
	 * @return bool
	 */
	public function is_404(): bool
	{
		return $this->query->is_404();
	}

	/**
	 * @return bool
	 */
	public function is_preview(): bool
	{
		return $this->query->is_preview();
	}

	/**
	 * @return bool
	 */
	public function is_paged(): bool
	{
		return $this->query->is_paged();
	}

	/**
	 * @param int $page
	 *
	 * @return bool
	 */
	public function is_current_page(int $page = 1): bool
	{
		return $this->has_pages() && ($page === $this->current_page());
	}

	/**
	 * @return bool
	 */
	public function has_pages(): bool
	{
		return $this->total_pages() > 1;
	}

	/**
	 * @return int
	 */
	public function current_page(): int
	{
		$current = (int) $this->query->get('paged', 1);

		return ($current === 0 && $this->has_pages()) ? 1 : $current;
	}

	/**
	 * @return int
	 */
	public function total_pages(): int
	{
		return (int) $this->query->max_num_pages;
	}

	/**
	 * @return int
	 */
	public function posts_per_page(): int
	{
		if ($this->has_pages()) {
			return ceil($this->total() / $this->total_pages());
		}

		return $this->total();
	}

	/**
	 * @return bool
	 */
	public static function is_admin(): bool
	{
		return is_admin();
	}

}
