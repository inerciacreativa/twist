<?php

namespace Twist\Model\Post;

use Twist\Model\ModelCollection;

/**
 * Class Query
 *
 * @package Twist\Model\Post
 */
class Query extends ModelCollection
{

	/**
	 * @var \WP_Query
	 */
	protected $query;

	/**
	 * @param array $query
	 *
	 * @return Query
	 */
	public static function make(array $query = null): Query
	{
		return new static(new \WP_Query($query));
	}

	/**
	 * Posts constructor.
	 *
	 * @param \WP_Query|null $query
	 */
	public function __construct(\WP_Query $query = null)
	{
		global $wp_query;

		$this->query = $query ?? $wp_query;

		parent::__construct();
	}

	/**
	 * @inheritdoc
	 */
	public function rewind()
	{
		$this->query->rewind_posts();
	}

	/**
	 * @inheritdoc
	 */
	public function next()
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
	public function is_post($post = null): bool
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