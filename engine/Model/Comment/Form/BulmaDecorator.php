<?php

namespace Twist\Model\Comment\Form;

use Twist\Library\Util\Tag;

/**
 * Class BulmaDecorator
 *
 * @package Twist\Model\Comment\Form
 */
class BulmaDecorator extends FormDecorator
{

	/**
	 * @inheritdoc
	 */
	public function defaults(): array
	{
		return [
			'class_form'    => 'form-comment form-standard',
			'submit_field'  => '<p class="field">%1$s%2$s</p>',
			'submit_button' => Tag::span(['class' => 'control'], Tag::input([
				'id'    => 'submit',
				'name'  => 'submit',
				'class' => 'button is-primary',
				'type'  => 'submit',
				'value' => '%4$s',
			])),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function field(string $type, string $name, string $label, array $attributes): Tag
	{
		return Tag::p(['class' => 'field'], [
			Tag::label(['for' => $name, 'class' => 'label'], $this->label($label, $attributes)),
			Tag::span(['class' => 'control'], Tag::make($type, array_merge(['id' => $name, 'name' => $name, 'class' => $type], $attributes))),
		]);
	}

}