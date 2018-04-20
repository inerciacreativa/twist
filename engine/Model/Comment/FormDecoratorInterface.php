<?php

namespace Twist\Model\Comment;

use Twist\Library\Util\Tag;

/**
 * Interface FormDecoratorInterface
 *
 * @package Twist\Model\Comment
 */
interface FormDecoratorInterface
{

	/**
	 * @param array $arguments
	 *
	 * @return array
	 */
	public function getDefaults(array $arguments): array;

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function getTextInput(string $id, string $label, array $attributes): Tag;

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function getTextArea(string $id, string $label, array $attributes): Tag;

	/**
	 * @param string $id
	 * @param string $text
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function getSubmitButton(string $id, string $text): Tag;

	/**
	 * @param string $id
	 * @param string $text
	 * @param string $form
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function getCancelButton(string $id, string $text, string $form): Tag;

}