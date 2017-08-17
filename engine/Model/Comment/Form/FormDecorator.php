<?php

namespace Twist\Model\Comment\Form;

use Twist\Library\Util\Tag;

/**
 * Class FormDecorator
 *
 * @package Twist\Model\Comment\Form
 */
abstract class FormDecorator implements FormDecoratorInterface
{

	/**
	 * @inheritdoc
	 */
	abstract public function field(string $type, string $name, string $label, array $attributes): Tag;

	/**
	 * @inheritdoc
	 */
	public function text(string $name, string $label, array $attributes): Tag
	{
		return $this->field('input', $name, $label, $attributes);
	}

	/**
	 * @inheritdoc
	 */
	public function textarea(string $name, string $label, array $attributes): Tag
	{
		return $this->field('textarea', $name, $label, $attributes);
	}

	/**
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return array
	 */
	protected function label(string $label, array $attributes): array
	{
		if (isset($attributes['required'])) {
			return [
				$label,
				' ',
				Tag::span(['class' => 'required'], '*'),
			];
		}

		return [$label];
	}

}