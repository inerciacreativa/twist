<?php

namespace Twist\Model\Post;

use Twist\Library\Hook\Hook;
use Twist\Model\User\User;
use WP_User;

/**
 * Class Author
 *
 * @package Twist\Model\Post
 */
class Author extends User
{

	/**
	 * @var Post
	 */
	private $post;

	/**
	 * Author constructor.
	 *
	 * @param int      $user
	 * @param Post     $post
	 *
	 * @global WP_User $authordata
	 */
	public function __construct($user = 0, Post $post = null)
	{
		global $authordata;

		$this->post = $post;

		parent::__construct($user ?: $authordata);
	}

	/**
	 * @return string
	 */
	public function link(): string
	{
		return get_author_posts_url($this->id(), $this->nice_name());
	}

	/**
	 * @inheritDoc
	 */
	public function name(): string
	{
		return Hook::apply('the_author', parent::name());
	}

	/**
	 * @inheritDoc
	 */
	public function first_name(): string
	{
		return $this->filter('first_name');
	}

	/**
	 * @inheritDoc
	 */
	public function last_name(): string
	{
		return $this->filter('last_name');
	}

	/**
	 * @inheritDoc
	 */
	public function email(): string
	{
		return $this->filter('user_email');
	}

	/**
	 * @inheritDoc
	 */
	public function url(): string
	{
		return esc_url($this->filter('user_url'));
	}

	/**
	 * @inheritDoc
	 */
	public function description(): string
	{
		return $this->filter('description');
	}

	/**
	 * @inheritDoc
	 */
	protected function query(array $query): Query
	{
		if ($this->post) {
			$query['post__not_in'] = [$this->post->id()];
		} else if (isset($GLOBALS['post'])) {
			$query['post__not_in'] = [$GLOBALS['post']->ID];
		}

		return parent::query($query);
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function filter(string $field): string
	{
		$value = Hook::apply("get_the_author_$field", $this->field($field), $this->id());

		return Hook::apply("the_author_$field", $value, $this->id());
	}

}
