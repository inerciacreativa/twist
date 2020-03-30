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
		return $this->getField('first_name');
	}

	/**
	 * @inheritDoc
	 */
	public function last_name(): string
	{
		return $this->getField('last_name');
	}

	/**
	 * @inheritDoc
	 */
	public function email(): string
	{
		return $this->getField('user_email');
	}

	/**
	 * @inheritDoc
	 */
	public function url(): string
	{
		return esc_url($this->getField('user_url'));
	}

	/**
	 * @inheritDoc
	 */
	public function description(): string
	{
		return $this->getField('description');
	}

	/**
	 * @inheritDoc
	 */
	protected function query(array $query): Query
	{
		if ($this->post) {
			$query['post__not_in'] = [$this->post->id()];
		}

		return parent::query($query);
	}

	/**
	 * @inheritDoc
	 */
	protected function getField(string $field): string
	{
		$value = Hook::apply("get_the_author_$field", parent::getField($field), $this->id());

		return Hook::apply("the_author_$field", $value, $this->id());
	}

}
