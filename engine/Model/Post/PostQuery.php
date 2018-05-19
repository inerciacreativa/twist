<?php

namespace Twist\Model\Post;

use Twist\Library\Model\IterableInterface;
use Twist\Library\Util\Arr;

/**
 * Class PostQuery
 *
 * @package Twist\Model\Post
 */
class PostQuery implements IterableInterface
{

	/**
	 * @var \WP_Query
	 */
	protected $query;

	/**
	 * @return PostQuery
	 */
	public static function main(): PostQuery
	{
		return new static();
	}

	/**
	 * @param array $query
	 *
	 * @return PostQuery
	 */
	public static function make(array $query): PostQuery
	{
		$parameters = array_merge($query, [
			'suppress_filters'    => true,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		]);

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

		return new static($parameters);
	}

	/**
	 * @param int   $number
	 * @param array $query
	 *
	 * @return PostQuery
	 */
	public static function latest(int $number = 3, array $query = []): PostQuery
	{
		$parameters = array_merge([
			'orderby'   => 'post_date',
			'order'     => 'DESC',
			'post_type' => 'post',
		], $query);

		$parameters['posts_per_page'] = $number;

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
	 * @return bool
	 */
	public function is_paged(): bool
	{
		return $this->query->is_paged();
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

}