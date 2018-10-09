<?php

namespace Twist\Model\Post;

use Twist\Library\Model\IterableInterface;
use Twist\Library\Util\Arr;

/**
 * Class PostQuery
 *
 * @package Twist\Model\Post
 */
class Query implements IterableInterface
{

	/**
	 * @var Query
	 */
	static protected $main;

	/**
	 * @var array
	 */
	static private $cache = [];

	/**
	 * @var \WP_Query
	 */
	protected $query;

	/**
	 * @return Query
	 */
	public static function main(): Query
	{
		if (static::$main === null) {
			static::$main = new static();
		}

		return static::$main;
	}

	/**
	 * @param array $parameters
	 * @param bool  $defaults
	 *
	 * @return Query
	 */
	public static function make(array $parameters, bool $defaults = true): Query
	{
		if ($defaults) {
			$parameters = array_merge($parameters, [
				'suppress_filters'    => true,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			]);
		}

		$parameters = array_merge([
			'post_type'      => 'post',
			'posts_per_page' => get_option('posts_per_page'),
		], $parameters);

		if (empty($parameters['post_status'])) {
			$parameters['post_status'] = ($parameters['post_type'] === 'attachment') ? 'inherit' : 'publish';
		}

		if (!empty($parameters['include'])) {
			$ids = wp_parse_id_list($parameters['include']);

			$parameters['posts_per_page'] = \count($ids);
			$parameters['post__in']       = $ids;
		} else if (!empty($parameters['exclude'])) {
			$parameters['post__not_in'] = wp_parse_id_list($parameters['exclude']);
		}

		$id = json_encode($parameters);
		if (!array_key_exists($id, static::$cache)) {
			static::$cache[$id] = new static($parameters);
		}

		return static::$cache[$id];
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
			$search  = preg_replace('/[^a-z ]/i', '', $search);
		}

		$parameters = array_merge([
			'post_type' => 'any',
			//'posts_per_page' => 5,
		], $query, [
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

		$this->query = $query ? new \WP_Query($query) : $wp_query;
	}

	/**
	 * @return \WP_Query
	 */
	public function object(): \WP_Query
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
	 * @return array|null
	 */
	public function get_comments(): ?array
	{
		return $this->query->comments;
	}

	/**
	 * @return bool
	 */
	public function has_comments(): bool
	{
		return !empty($this->query->comments);
	}

	/**
	 * @param array $comments
	 * @param int   $pages
	 */
	public function set_comments(array $comments, $pages): void
	{
		$this->query->comments              = $comments;
		$this->query->comment_count         = \count($comments);
		$this->query->max_num_comment_pages = (int) $pages;
	}

	/**
	 * @return int
	 */
	public function comment_count(): int
	{
		return (int) $this->query->comment_count;
	}

	/**
	 * @return int
	 */
	public function comment_pages(): int
	{
		return (int) $this->query->max_num_comment_pages;
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
	public function is_home(): bool
	{
		return $this->query->is_home();
	}

	/**
	 * @return bool
	 */
	public function is_front(): bool
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
		return $this->query->is_single($post_type);
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
	 * @param null|string|array $post_type
	 *
	 * @return bool
	 */
	public function is_archive($post_type = null): bool
	{
		if ($post_type) {
			return $this->query->is_post_type_archive($post_type);
		}

		return $this->query->is_archive();
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
	 *
	 * @return bool
	 */
	public function is_taxonomy($taxonomy = null): bool
	{
		return $this->query->is_tax($taxonomy);
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
		return $this->has_pages() && ($page === (int) $this->query->get('paged', 1));
	}

	/**
	 * @return bool
	 */
	public function has_pages(): bool
	{
		return $this->query->max_num_pages > 1;
	}

}