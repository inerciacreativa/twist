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
	 * @return array
	 */
	public function defaults(): array;

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function field(string $type, string $name, string $label, array $attributes): Tag;

	/**
	 * @param string $name
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function text(string $name, string $label, array $attributes): Tag;

	/**
	 * @param string $name
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return \Twist\Library\Util\Tag
	 */
	public function textarea(string $name, string $label, array $attributes): Tag;

}