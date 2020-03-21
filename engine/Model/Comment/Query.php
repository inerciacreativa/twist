<?php

namespace Twist\Model\Comment;

use Twist\Library\Hook\Hook;
use Twist\Model\Post\Post;
use Twist\Twist;
use WP_Query;

/**
 * Class Query
 *
 * @package Twist\Model\Comment
 */
class Query
{

	public const ALL = 'all';

	public const COMMENTS = 'comment';

	public const PINGS = 'pings';

	/**
	 * @var Post
	 */
	private $post;

	/**
	 * @var bool
	 */
	private $loaded = false;

	/**
	 * Query constructor.
	 *
	 * @param Post $post
	 */
	public function __construct(Post $post)
	{
		$this->post = $post;
		$this->load();
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
	 * @param string $type
	 * @param array  $arguments
	 *
	 * @return Comments
	 */
	private function build(string $type, array $arguments = []): Comments
	{
		$builder = new Builder($this, $type);

		if ($this->loaded) {
			wp_list_comments(array_merge($arguments, [
				'type'      => $type,
				'walker'    => $builder,
				'max_depth' => $this->max_depth(),
				'echo'      => false,
			]));
		}

		return $builder->getComments();
	}

	/**
	 * @return Comments
	 */
	public function all(): Comments
	{
		return $this->build(self::ALL);
	}

	/**
	 * @return Comments
	 */
	public function comments(): Comments
	{
		return $this->build(self::COMMENTS);
	}

	/**
	 * @return Comments
	 */
	public function pings(): Comments
	{
		return $this->build(self::PINGS);
	}

	/**
	 * @return string
	 */
	public function form(): string
	{
		return new Form();
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
		return $this->post->comment_count();
	}

	/**
	 * @return int
	 */
	private function max_depth(): int
	{
		static $max_depth;

		if ($max_depth === null) {
			if (get_option('thread_comments')) {
				$max_depth = (int) get_option('thread_comments_depth');
			} else {
				$max_depth = -1;
			}
		}

		return $max_depth;
	}

}
