<?php

namespace Twist\Model\Comment;

use IteratorAggregate;
use Twist\App\AppException;
use Twist\Library\Hook\Hook;
use Twist\Model\CollectionIteratorInterface;
use Twist\Model\Pagination\HasPaginationInterface;
use Twist\Model\Pagination\PaginationInterface;
use Twist\Model\Post\Post;
use Twist\Model\Post\Query as PostQuery;
use Twist\Twist;
use WP_Query;

/**
 * Class Query
 *
 * @package Twist\Model\Comment
 */
class Query implements HasPaginationInterface, IteratorAggregate
{

	/**
	 * @var Post
	 */
	private $post;

	/**
	 * @var bool
	 */
	private $loaded = false;

	/**
	 * @var Comments
	 */
	private $comments;

	/**
	 * @var Pagination
	 */
	private $pagination;

	/**
	 * Query constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		$this->post = $post;

		$this->load();
		$this->build();
	}

	/**
	 * Load the comments in the global @var WP_Query object.
	 */
	private function load(): void
	{
		Hook::add('comments_template', function () {
			// If this filter is fired then the comments has been loaded.
			$this->loaded = true;

			// Load empty comments.php from parent theme always.
			return Twist::config('dir.template') . '/comments.php';
		});

		comments_template();
	}

	/**
	 * Build the Comments collection.
	 */
	private function build(): void
	{
		$builder = new Builder($this);

		if ($this->loaded) {
			wp_list_comments([
				'walker'    => $builder,
				'max_depth' => $this->max_depth(),
				'echo'      => false,
			]);
		}

		$this->comments = $builder->getComments();
	}

	/**
	 * @return int
	 */
	private function max_depth(): int
	{
		if (get_option('thread_comments')) {
			return (int) get_option('thread_comments_depth');
		}

		return -1;
	}

	/**
	 * @return Comments
	 */
	public function comments(): Comments
	{
		return $this->comments;
	}

	/**
	 * @param array $classes
	 * @param array $ids
	 *
	 * @return string
	 */
	public function form(array $classes = [], array $ids = []): string
	{
		return new Form($classes, $ids);
	}

	/**
	 * @return Post
	 */
	public function post(): Post
	{
		return $this->post;
	}

	/**
	 * @inheritDoc
	 */
	public function total(): int
	{
		return $this->post->comment_count();
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int
	{
		return $this->comments->count();
	}

	/**
	 * @inheritDoc
	 */
	public function per_page(): int
	{
		static $per_page;

		if (!isset($per_page)) {
			try {
				$per_page = (int) PostQuery::main()->get('comments_per_page');
			} catch (AppException $exception) {
				$per_page = 0;
			}

			if (0 === $per_page) {
				$per_page = (int) get_option('comments_per_page');
			}
		}

		return $per_page;
	}

	/**
	 * @inheritDoc
	 */
	public function total_pages(): int
	{
		static $total_pages;

		if (!isset($total_pages)) {
			$total_pages = (int) get_comment_pages_count();
		}

		return $total_pages;
	}

	/**
	 * @inheritDoc
	 */
	public function current_page(): int
	{
		try {
			return max(1, (int) PostQuery::main()->get('cpage'));
		} catch (AppException $exception) {
		}

		return 1;
	}

	/**
	 * @return int
	 */
	public function first_page(): int
	{
		static $first_page;

		if (!isset($first_page)) {
			$first_page = (get_option('default_comments_page') === 'newest') ? $this->total_pages() : 1;
		}

		return $first_page;
	}

	/**
	 * @inheritDoc
	 */
	public function has_pagination(): bool
	{
		return $this->total_pages() > 1;
	}

	/**
	 * @inheritDoc
	 */
	public function pagination(): PaginationInterface
	{
		return $this->pagination ?? $this->pagination = new Pagination($this);
	}

	/**
	 * @return Iterator
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return $this->comments->getIterator();
	}

}
