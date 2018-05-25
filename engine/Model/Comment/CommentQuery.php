<?php

namespace Twist\Model\Comment;

use Twist\Library\Hook\Hook;
use Twist\Model\Post\Post;
use Twist\Model\User\User;
use function Twist\config;

/**
 * Class CommentQuery
 *
 * @package Twist\Model\Comment
 */
class CommentQuery
{

	/**
	 * @var Post
	 */
	protected $post;

	/**
	 * @var bool
	 */
	protected $is_ready;

	/**
	 * @var array
	 */
	protected $arguments;

	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * @var int
	 */
	protected $max_depth = -1;

	/**
	 * @var int
	 */
	protected $per_page = 0;

	/**
	 * @var int
	 */
	protected $page = 0;

	/**
	 * @var int
	 */
	protected $page_total = 0;

	/**
	 * @var int
	 */
	protected $page_first = 0;

	/**
	 * @var string
	 */
	protected $page_default = '';

	/**
	 * @var string
	 */
	protected $order = '';

	/**
	 * @var bool
	 */
	protected $is_threaded = false;

	/**
	 * @var bool
	 */
	protected $is_paged = false;

	/**
	 * @var bool
	 */
	protected $is_page_override = false;

	/**
	 * @var CommentPagination
	 */
	protected $pagination;

	/**
	 * CommentQuery constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		$this->post        = $post;
		$this->count       = (int) Hook::apply('get_comments_number', $post->field('comment_count'), $post->id());
		$this->is_threaded = (bool) get_option('thread_comments');
		$this->is_paged    = (bool) get_option('page_comments');
		$this->order       = get_option('comment_order');

		if ($this->is_threaded) {
			$this->max_depth = (int) get_option('thread_comments_depth');
		}

		if ($this->is_paged) {
			$this->page_default = (string) get_option('default_comments_page');

			$this->per_page = (int) get_query_var('comments_per_page');
			if ($this->per_page === 0) {
				$this->per_page = (int) get_option('comments_per_page');
			}

			$this->page = (int) get_query_var('cpage');
		}

		$this->is_ready  = $this->setup();
		$this->arguments = $this->arguments();

		if ($this->is_paged) {
			$this->page_total = $this->page_total($this->arguments);
			$this->page_first = ($this->page_default === 'newest') ? $this->page_total : 1;
		}
	}

	/**
	 * @see wp_list_comments()
	 *
	 * @return Comments
	 */
	public function get(): ?Comments
	{
		if (!$this->is_ready) {
			return null;
		}

		$main_query = $this->main_query();
		$comments   = &$main_query->comments;

		if (empty($main_query->comments)) {
			return null;
		}

		wp_queue_comments_for_comment_meta_lazyload($comments);

		$walker = new CommentWalker(new Comments($this));
		$walker->paged_walk($comments, $this->max_depth, $this->page, $this->per_page, $this->arguments);

		return $walker->comments();
	}

	/**
	 * @return bool
	 */
	public function has_pagination(): bool
	{
		return $this->is_ready && $this->is_paged && ($this->page_total > 1);
	}

	/**
	 * @return null|CommentPagination
	 */
	public function pagination(): CommentPagination
	{
		if ($this->pagination === null) {
			$this->pagination = new CommentPagination($this->page_total, $this->page_first, $this->post->link());
		}

		return $this->pagination;
	}

	/**
	 * @return string
	 */
	public function form(): string
	{
		$form = new CommentForm(config('view.form_decorator') ?? new CommentFormDecorator());

		return $form->show();
	}

	/**
	 * @return Post
	 */
	public function post(): Post
	{
		return $this->post;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return $this->count;
	}

	/**
	 * @return int
	 */
	public function max_depth(): int
	{
		return $this->max_depth;
	}

	/**
	 * @return bool
	 */
	public function are_open(): bool
	{
		return Hook::apply('comments_open', $this->post->field('comment_status') === 'open', $this->post->id());
	}

	/**
	 * @see comments_template()
	 *
	 * @return bool
	 */
	protected function setup(): bool
	{
		$main_query = $this->main_query();

		if (!$this->post->id() || !($main_query->is_single() || $main_query->is_page())) {
			return false;
		}

		$arguments = [
			'no_found_rows'             => false,
			'update_comment_meta_cache' => false,
			'hierarchical'              => $this->is_threaded ? 'threaded' : false,
			'status'                    => 'approve',
			'orderby'                   => 'comment_date_gmt',
		];

		if ($this->is_paged) {
			$arguments['number'] = $this->per_page;
			$arguments['offset'] = $this->query_offset();
		}

		$comment_query = $this->query($arguments, false);
		$comments      = $this->is_threaded ? $this->flatten($comment_query->comments, $arguments) : $comment_query->comments;

		$main_query->comments              = Hook::apply('comments_array', $comments, $this->post->id());
		$main_query->comment_count         = \count($main_query->comments);
		$main_query->max_num_comment_pages = (int) $comment_query->max_num_pages;

		if ($this->page === 0 && $main_query->max_num_comment_pages > 1) {
			$this->page             = ($this->page_default === 'newest') ? $main_query->max_num_comment_pages : 1;
			$this->is_page_override = true;

			set_query_var('cpage', $this->page);
		}

		return !empty($main_query->comments);
	}

	/**
	 * @return array
	 */
	protected function arguments(): array
	{
		$main_query = $this->main_query();

		$arguments = [
			'type'              => 'all',
			'page'              => '',
			'per_page'          => '',
			'max_depth'         => $this->is_threaded ? $this->max_depth : -1,
			'reverse_top_level' => $this->order === 'desc',
		];

		if ($main_query->max_num_comment_pages) {
			if ($this->page_default === 'newest') {
				$arguments['cpage'] = $this->page;
			} else if ($this->page === 1) {
				$arguments['cpage'] = '';
			} else {
				$arguments['cpage'] = $this->page;
			}

			$arguments['page']     = 0;
			$arguments['per_page'] = 0;
		}

		if ($this->is_paged && $arguments['per_page'] === '') {
			$arguments['per_page'] = $this->per_page;
		}

		if (empty($arguments['per_page'])) {
			$arguments['page']     = 0;
			$arguments['per_page'] = 0;
		}

		if ($arguments['page'] === '') {
			if ($this->is_page_override) {
				$arguments['page'] = ($this->page_default === 'newest') ? $this->page_count($arguments, true) : 1;
			} else {
				$arguments['page'] = $this->page;
			}
		}

		if ($arguments['page'] === 0 && $arguments['per_page'] !== 0) {
			$arguments['page'] = 1;
		}

		$this->page      = $arguments['page'];
		$this->per_page  = $arguments['per_page'];
		$this->max_depth = $arguments['max_depth'];

		return $arguments;
	}

	protected function rewrite(): \WP_Rewrite
	{
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;

		return $wp_rewrite;
	}

	/**
	 * @return \WP_Query
	 */
	protected function main_query(): \WP_Query
	{
		/** @var \WP_Query $wp_query */
		global $wp_query;

		return $wp_query;
	}

	/**
	 * @param array $arguments
	 * @param bool  $results
	 *
	 * @return \WP_Comment_Query|array|int
	 */
	protected function query(array $arguments = [], $results = true)
	{
		$arguments = array_merge([
			'post_id'            => $this->post->id(),
			'orderby'            => 'comment_date_gmt',
			'order'              => 'ASC',
			'status'             => 'approve',
			'include_unapproved' => $this->user(),
		], $arguments);

		if (!$results) {
			return new \WP_Comment_Query($arguments);
		}

		return (new \WP_Comment_Query())->query($arguments);
	}

	/**
	 * @return int
	 */
	protected function query_offset(): int
	{
		if ($this->page) {
			return ($this->page - 1) * $this->per_page;
		}

		if ($this->page_default === 'oldest') {
			return 0;
		}

		$count = $this->query([
			'count'   => true,
			'orderby' => false,
			'parent'  => $this->is_threaded ? 0 : '',
		]);

		return (int) (ceil($count / $this->per_page) - 1) * $this->per_page;
	}

	/**
	 * @param \WP_Comment[] $comments
	 * @param array         $arguments
	 *
	 * @return \WP_Comment[]
	 */
	protected function flatten(array $comments, array $arguments): array
	{
		$flattened = [[]];

		foreach ($comments as $comment) {
			$children = $comment->get_children([
				'format'  => 'flat',
				'status'  => $arguments['status'],
				'orderby' => $arguments['orderby'],
			]);

			$flattened[] = [$comment];
			$flattened[] = $children;
		}

		return array_merge(...$flattened);
	}

	/**
	 * @param array $arguments
	 * @param bool  $set
	 *
	 * @return int
	 */
	protected function page_count(array $arguments, bool $set = false): int
	{
		if (!$this->is_paged || $arguments['per_page'] === 0) {
			return 1;
		}

		$main_query = $this->main_query();

		if ($arguments['max_depth'] !== 1) {
			// Count root comments
			$count = array_reduce($main_query->comments, function (int $count, \WP_Comment $comment) {
				if ($comment->comment_parent === 0) {
					$count++;
				}

				return $count;
			}, 0);
		} else {
			// Count all comments
			$count = \count($main_query->comments);
		}

		$count = (int) ceil($count / $arguments['per_page']);

		if ($set) {
			set_query_var('cpage', $count);
		}

		return $count;
	}

	/**
	 * @param array $arguments
	 *
	 * @return int
	 */
	protected function page_total(array $arguments): int
	{
		$main_query = $this->main_query();

		if (!empty($main_query->max_num_comment_pages)) {
			return $main_query->max_num_comment_pages;
		}

		return $this->page_count($arguments);
	}

	/**
	 * @return int|string
	 */
	protected function user()
	{
		$user = User::current();

		if ($user->exists()) {
			return $user->id();
		}

		if (!empty($user->email())) {
			return $user->email();
		}

		return '';
	}

}