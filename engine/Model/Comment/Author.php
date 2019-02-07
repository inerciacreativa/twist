<?php

namespace Twist\Model\Comment;

use Twist\Library\Hook\Hook;
use Twist\Model\User\User;

/**
 * Class Author
 *
 * @package Twist\Model\Comment
 */
class Author extends User
{

	/**
	 * @var Comment
	 */
	private $comment;

	/**
	 * Author constructor.
	 *
	 * @param Comment $comment
	 */
	public function __construct(Comment $comment)
	{
		parent::__construct($comment->user_id());

		$this->setup($comment);
	}

	/**
	 * @param Comment $comment
	 */
	private function setup(Comment $comment): void
	{
		$this->comment = $comment;

		$properties = [
			'display_name' => 'comment_author',
			'user_email'   => 'comment_author_email',
			'user_url'     => 'comment_author_url',
		];

		foreach ($properties as $property => $variable) {
			$value = $comment->object()->$variable;

			if ($property === 'display_name' && empty($value)) {
				$value = $this->exists() ? $this->field('display_name') : __('Anonymous');
			} else if ($property === 'user_url') {
				$value = ('http://' === $value) ? '' : esc_url($value, [
					'http',
					'https',
				]);
			}

			$this->field($property, Hook::apply('get_' . $variable, $value, $comment->id(), $comment->object()));
		}
	}

	/**
	 * @inheritdoc
	 */
	public function name(): string
	{
		return Hook::apply('comment_author', parent::name(), $this->comment->id());
	}

	/**
	 * @inheritdoc
	 */
	public function email(): string
	{
		return Hook::apply('author_email', parent::email(), $this->comment->id());
	}

	/**
	 * @inheritdoc
	 */
	public function url(): string
	{
		return esc_url(Hook::apply('comment_url', parent::url(), $this->comment->id()));
	}

}