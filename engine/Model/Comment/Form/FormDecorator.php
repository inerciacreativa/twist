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
	public function getTextInput(string $id, string $label, array $attributes): Tag
	{
		return $this->getField('input', $id, $label, $attributes);
	}

	/**
	 * @inheritdoc
	 */
	public function getTextArea(string $id, string $label, array $attributes): Tag
	{
		return $this->getField('textarea', $id, $label, $attributes);
	}

	/**
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return array
	 */
	protected function getLabel(string $label, array $attributes): array
	{
		if (isset($attributes['required'])) {
			return [$label, ' ', Tag::span(['class' => 'required'], '*')];
		}

		return [$label];
	}

}