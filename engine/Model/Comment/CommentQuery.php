<?php

namespace Twist\Model\Comment;

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
	protected $setup;

	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * @var string
	 */
	protected $order = '';

	/**
	 * @var bool
	 */
	protected $threaded = false;

	/**
	 * @var int
	 */
	protected $max_depth = -1;

	/**
	 * @var bool
	 */
	protected $paged = false;

	/**
	 * @var int
	 */
	protected $per_page = 0;

	/**
	 * @var int
	 */
	protected $page = 0;

	/**
	 * @var bool
	 */
	protected $page_override = false;

	/**
	 * @var string
	 */
	protected $page_default = '';

	/**
	 * CommentQuery constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		$this->post     = $post;
		$this->count    = (int) apply_filters('get_comments_number', $post->field('comment_count'), $post->id());
		$this->threaded = (bool) get_option('thread_comments');
		$this->paged    = (bool) get_option('page_comments');
		$this->order    = get_option('comment_order');

		if ($this->threaded) {
			$this->max_depth = (int) get_option('thread_comments_depth');
		}

		if ($this->paged) {
			$this->page_default = (string) get_option('default_comments_page');

			$this->per_page = (int) get_query_var('comments_per_page');
			if ($this->per_page === 0) {
				$this->per_page = (int) get_option('comments_per_page');
			}

			$this->page = (int) get_query_var('cpage');
		}
	}

	public function __invoke(array $arguments = []): ?Comments
	{
		return $this->all();
	}

	/**
	 * @return Comments
	 */
	public function comments(): ?Comments
	{
		return $this->all(['type' => 'comment']);
	}

	/**
	 * @return Comments
	 */
	public function pings(): ?Comments
	{
		return $this->all(['type' => 'pings']);
	}

	/**
	 * @see wp_list_comments()
	 *
	 * @param array $arguments
	 *
	 * @return Comments
	 */
	public function all(array $arguments = []): ?Comments
	{
		if (!$this->setup()) {
			return null;
		}

		$arguments = array_merge([
			'max_depth'         => '',
			'type'              => 'all',
			'page'              => '',
			'per_page'          => '',
			'reverse_top_level' => null,
			'reverse_children'  => '',
		], $arguments);

		$arguments = (array) apply_filters('wp_list_comments_args', $arguments);

		if (($arguments['page'] || $arguments['per_page']) && ($arguments['page'] !== $this->page || $arguments['per_page'] !== $this->per_page)) {
			$comments = $this->parse($arguments['type']);

			if (empty($comments)) {
				return null;
			}
		} else {
			$mainQuery = $this->main_query();
			$comments  = $this->parse($arguments['type'], $mainQuery);

			if (empty($comments)) {
				return null;
			}

			if ($mainQuery->max_num_comment_pages) {
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
		}

		wp_queue_comments_for_comment_meta_lazyload($comments);

		return $this->get($comments, $arguments);
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
		return apply_filters('comments_open', $this->post->field('comment_status') === 'open', $this->post->id());
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
	 * @see comments_template()
	 *
	 * @return bool
	 */
	protected function setup(): bool
	{
		if ($this->setup !== null) {
			return $this->setup;
		}

		$main_query = $this->main_query();

		if (!$this->post->id() || !($main_query->is_single() || $main_query->is_page())) {
			return $this->setup = false;
		}

		$arguments = [
			'no_found_rows'             => false,
			'update_comment_meta_cache' => false,
			'hierarchical'              => $this->threaded ? 'threaded' : false,
			'status'                    => 'approve',
			'orderby'                   => 'comment_date_gmt',
		];

		if ($this->paged) {
			$arguments['number'] = $this->per_page;
			$arguments['offset'] = $this->query_offset();
		}

		$comment_query = $this->query($arguments, false);
		$comments      = $this->threaded ? $this->flatten($comment_query->comments, $arguments) : $comment_query->comments;

		$main_query->comments              = apply_filters('comments_array', $comments, $this->post->id());
		$main_query->comment_count         = \count($main_query->comments);
		$main_query->max_num_comment_pages = (int) $comment_query->max_num_pages;

		if ($this->page === 0 && $main_query->max_num_comment_pages > 1) {
			$this->page          = ($this->page_default === 'newest') ? $main_query->max_num_comment_pages : 1;
			$this->page_override = true;

			set_query_var('cpage', $this->page);
		}

		return $this->setup = true;
	}

	/**
	 * @param string         $type
	 * @param \WP_Query|null $query
	 *
	 * @return \WP_Comment[]
	 */
	protected function parse($type, \WP_Query $query = null): array
	{
		if ($query) {
			$comments = &$query->comments;
		} else {
			$comments = $this->query();
		}

		if (empty($comments)) {
			return [];
		}

		if ($type === 'all') {
			return $comments;
		}

		if ($query && !empty($query->comments_by_type)) {
			$comments_by_type = $query->comments_by_type;
		} else {
			$comments_by_type = separate_comments($comments);
		}

		if ($query && empty($query->comments_by_type)) {
			$query->comments_by_type = $comments_by_type;
		}

		if (empty($comments_by_type[$type])) {
			return [];
		}

		return $comments_by_type[$type];
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
			'parent'  => $this->threaded ? 0 : '',
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
			$children    = $comment->get_children([
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
	 * @param \WP_Comment[] $comments
	 * @param array         $arguments
	 *
	 * @return Comments
	 */
	protected function get(array $comments, array $arguments): Comments
	{
		if ($this->paged && $arguments['per_page'] === '') {
			$arguments['per_page'] = $this->per_page;
		}

		if (empty($arguments['per_page'])) {
			$arguments['page']     = 0;
			$arguments['per_page'] = 0;
		}

		if ($arguments['max_depth'] === '') {
			$arguments['max_depth'] = $this->threaded ? $this->max_depth : -1;
		} else {
			$arguments['max_depth'] = (int) $arguments['max_depth'];
		}

		if ($arguments['page'] === '') {
			if ($this->page_override) {
				$arguments['page'] = ($this->page_default === 'newest') ? $this->page_count($comments, $arguments) : 1;
			} else {
				$arguments['page'] = $this->page;
			}
		}

		if ($arguments['page'] === 0 && $arguments['per_page'] !== 0) {
			$arguments['page'] = 1;
		}

		if ($arguments['reverse_top_level'] === null) {
			$arguments['reverse_top_level'] = ($this->order === 'desc');
		}

		$this->page      = $arguments['page'];
		$this->per_page  = $arguments['per_page'];
		$this->max_depth = $arguments['max_depth'];

		$walker = new CommentWalker(new Comments($this));
		$walker->paged_walk($comments, $this->max_depth, $this->page, $this->per_page, $arguments);

		return $walker->comments();
	}

	/**
	 * @param \WP_Comment[] $comments
	 * @param array         $arguments
	 *
	 * @return int
	 */
	protected function page_count(array $comments, array $arguments): int
	{
		if (empty($comments)) {
			return 0;
		}

		if (!$this->paged || $arguments['per_page'] === 0) {
			return 1;
		}

		if ($arguments['max_depth'] !== 1) {
			// Count root comments
			$count = array_reduce($comments, function (int $count, \WP_Comment $comment) {
				if ($comment->comment_parent === 0) {
					$count++;
				}

				return $count;
			}, 0);
		} else {
			// Count all comments
			$count = \count($comments);
		}

		$count = (int) ceil($count / $arguments['per_page']);

		set_query_var('cpage', $count);

		return $count;
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