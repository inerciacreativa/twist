<?php

namespace Twist\Model\Comment;

use Twist\Model\Post\Post;
use Twist\Model\User\User;

/**
 * Class Query
 *
 * @package Twist\Model\Comment
 */
class Query
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
     * Query constructor.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post     = $post;
        $this->count    = (int)apply_filters('get_comments_number', $post->field('comment_count'), $post->id());
        $this->threaded = (bool)get_option('thread_comments');
        $this->paged    = (bool)get_option('page_comments');
        $this->order    = get_option('comment_order');

        if ($this->threaded) {
            $this->max_depth = (int)get_option('thread_comments_depth');
        }

        if ($this->paged) {
            $this->page_default = (string)get_option('default_comments_page');

            $this->per_page = (int)get_query_var('comments_per_page');
            if ($this->per_page === 0) {
                $this->per_page = (int)get_option('comments_per_page');
            }

            $this->page = (int)get_query_var('cpage');
        }
    }

    /**
     * @return Comments
     */
    public function comments(): Comments
    {
        return $this->all(['type' => 'comment']);
    }

    /**
     * @return Comments
     */
    public function pings(): Comments
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
    public function all(array $arguments = []): Comments
    {
        $comments  = new Comments($this);

        if (!$this->setup()) {
            return $comments;
        }

        $arguments = array_merge([
            'max_depth'         => '',
            'type'              => 'all',
            'page'              => '',
            'per_page'          => '',
            'reverse_top_level' => null,
            'reverse_children'  => '',
        ], $arguments);

        $arguments = (array)apply_filters('wp_list_comments_args', $arguments);

        if (
            ($arguments['page'] || $arguments['per_page'])
            && ($arguments['page'] !== $this->page || $arguments['per_page'] !== $this->per_page)
        ) {
            $commentsArray = $this->getCommentsArray($arguments['type']);

            if (empty($commentsArray)) {
                return $comments;
            }
        } else {
            $mainQuery     = $this->getMainQuery();
            $commentsArray = $this->getCommentsArray($arguments['type'], $mainQuery);

            if (empty($commentsArray)) {
                return $comments;
            }

            if ($mainQuery->max_num_comment_pages) {
                if ($this->page_default === 'newest') {
                    $arguments['cpage'] = $this->page;
                } elseif ($this->page === 1) {
                    $arguments['cpage'] = '';
                } else {
                    $arguments['cpage'] = $this->page;
                }

                $arguments['page']     = 0;
                $arguments['per_page'] = 0;
            }
        }

        wp_queue_comments_for_comment_meta_lazyload($commentsArray);

        return $this->getComments($comments, $commentsArray, $arguments);
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
     * @param string $decorator
     *
     * @return string
     */
    public function form(string $decorator = null): string
    {
        $form = new Form($decorator);

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

        if (!$this->post->id() || !($this->getMainQuery()->is_single() || $this->getMainQuery()->is_page())) {
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
            $arguments['offset'] = $this->getQueryOffset();
        }

        $mainQuery    = $this->getMainQuery();
        $commentQuery = $this->getCommentQuery($arguments);
        $comments     = $this->threaded ? $this->getCommentsFlattened($commentQuery->comments, $arguments) : $commentQuery->comments;

        $mainQuery->comments              = apply_filters('comments_array', $comments, $this->post->id());
        $mainQuery->comment_count         = count($mainQuery->comments);
        $mainQuery->max_num_comment_pages = (int)$commentQuery->max_num_pages;

        if ($this->page === 0 && $mainQuery->max_num_comment_pages > 1) {
            $this->page          = ($this->page_default === 'newest') ? $mainQuery->max_num_comment_pages : 1;
            $this->page_override = true;

            set_query_var('cpage', $this->page);
        }

        return $this->setup = true;
    }

    /**
     * @return \WP_Query
     */
    protected function getMainQuery(): \WP_Query
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
    protected function getCommentQuery(array $arguments = [], $results = false)
    {
        $arguments = array_merge([
            'post_id'            => $this->post->id(),
            'orderby'            => 'comment_date_gmt',
            'order'              => 'ASC',
            'status'             => 'approve',
            'include_unapproved' => $this->getUser(),
        ], $arguments);

        if (!$results) {
            return new \WP_Comment_Query($arguments);
        }

        $query = new \WP_Comment_Query();

        return $query->query($arguments);
    }

    /**
     * @return int
     */
    protected function getQueryOffset(): int
    {
        if ($this->page) {
            return ($this->page - 1) * $this->per_page;
        }

        if ($this->page_default === 'oldest') {
            return 0;
        }

        $count = $this->getCommentQuery([
            'count'   => true,
            'orderby' => false,
            'parent'  => $this->threaded ? 0 : '',
        ], true);

        return (int)(ceil($count / $this->per_page) - 1) * $this->per_page;
    }

    /**
     * @param string         $type
     * @param \WP_Query|null $query
     *
     * @return \WP_Comment[]
     */
    protected function getCommentsArray($type, \WP_Query $query = null): array
    {
        if ($query) {
            $comments = &$query->comments;
        } else {
            $comments = $this->getCommentQuery([], true);
        }

        if (empty($comments)) {
            return [];
        }

        if ($type === 'all') {
            return $comments;
        }

        if ($query && !empty($query->comments_by_type)) {
            $commentsByType = $query->comments_by_type;
        } else {
            $commentsByType = separate_comments($comments);
        }

        if ($query && empty($query->comments_by_type)) {
            $query->comments_by_type = $commentsByType;
        }

        if (empty($commentsByType[$type])) {
            return [];
        }

        return $commentsByType[$type];
    }

    /**
     * @param \WP_Comment[] $comments
     * @param array         $arguments
     *
     * @return \WP_Comment[]
     */
    protected function getCommentsFlattened(array $comments, array $arguments): array
    {
        $flattened = [];

        foreach ($comments as $comment) {
            $flattened[] = $comment;
            $children    = $comment->get_children([
                'format'  => 'flat',
                'status'  => $arguments['status'],
                'orderby' => $arguments['orderby'],
            ]);

            foreach ($children as $child) {
                $flattened[] = $child;
            }
        }

        return $flattened;
    }

    /**
     * @param Comments $comments
     * @param array    $commentsArray
     * @param array    $arguments
     *
     * @return Comments
     */
    protected function getComments(Comments $comments, array $commentsArray, array $arguments): Comments
    {
        if ($this->paged && $arguments['per_page'] === '') {
            $arguments['per_page'] = $this->per_page;
        }

        if (empty($arguments['per_page'])) {
            $arguments['page']     = 0;
            $arguments['per_page'] = 0;
        } else {
            $arguments['per_page'] = (int)$arguments['per_page'];
        }

        if ($arguments['max_depth'] === '') {
            $arguments['max_depth'] = $this->threaded ? $this->max_depth : -1;
        } else {
            $arguments['max_depth'] = (int)$arguments['max_depth'];
        }

        if ($arguments['page'] === '') {
            if ($this->page_override) {
                $arguments['page'] = ($this->page_default === 'newest') ? $this->getPagesCount($commentsArray, $arguments) : 1;
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

        $walker = new Walker($comments);
        $walker->paged_walk($commentsArray, $this->max_depth, $this->page, $this->per_page, $arguments);

        return $comments;
    }

    /**
     * @param array $comments
     * @param array $arguments
     *
     * @return int
     */
    protected function getPagesCount(array $comments, array $arguments): int
    {
        if (empty($comments)) {
            return 0;
        }

        if (!$this->paged || $arguments['per_page'] === 0) {
            return 1;
        }

        if ($arguments['max_depth'] !== 1) {
            $walker = new Walker();
            $count  = ceil($walker->get_number_of_root_elements($comments) / $arguments['per_page']);
        } else {
            $count = ceil(count($comments) / $arguments['per_page']);
        }

        set_query_var('cpage', $count);

        return (int)$count;
    }

    /**
     * @return int|string
     */
    protected function getUser()
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