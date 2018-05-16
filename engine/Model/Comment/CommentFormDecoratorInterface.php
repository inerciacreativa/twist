<?php

namespace Twist\Model\Comment;

use Twist\Library\Util\Tag;

/**
 * Interface CommentFormDecoratorInterface
 *
 * @package Twist\Model\Comment
 */
interface CommentFormDecoratorInterface
{

	/**
	 * @param array $arguments
	 *
	 * @return array
	 */
	public function defaults(array $arguments): array;

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function text(string $id, string $label, array $attributes): Tag;

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function textarea(string $id, string $label, array $attributes): Tag;

	/**
	 * @param string $id
	 * @param string $text
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function submit(string $id, string $text): Tag;

	/**
	 * @param string $id
	 * @param string $text
	 * @param string $form
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function cancel(string $id, string $text, string $form): Tag;

}