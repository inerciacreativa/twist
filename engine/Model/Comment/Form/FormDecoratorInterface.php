<?php

namespace Twist\Model\Comment\Form;

use Twist\Library\Util\Tag;

/**
 * Class BootstrapDecorator
 *
 * @package Twist\Model\Comment\Form
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
	 * @param string $type
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function getField(string $type, string $id, string $label, array $attributes): Tag;

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
	 * @param string $label
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function getSubmitButton(string $id, string $label): Tag;

	/**
	 * @param string $id
	 * @param string $label
	 * @param string $form
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function getCancelButton(string $id, string $label, string $form): Tag;

}