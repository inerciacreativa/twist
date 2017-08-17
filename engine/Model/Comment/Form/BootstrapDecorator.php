<?php

namespace Twist\Model\Comment\Form;

use Twist\Library\Util\Tag;

/**
 * Class BootstrapDecorator
 *
 * @package Twist\Model\Comment\Form
 */
class BootstrapDecorator extends FormDecorator
{

	/**
	 * @inheritdoc
	 */
	public function defaults(): array
	{
		return [
			'class_form'    => 'form-comment form-standard',
			'submit_field'  => '<p class="form-actions">%1$s%2$s</p>',
			'submit_button' => Tag::input([
				'id'    => 'submit',
				'name'  => 'submit',
				'class' => 'btn btn-primary',
				'type'  => 'submit',
				'value' => '%4$s',
			]),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function field(string $type, string $name, string $label, array $attributes): Tag
	{
		return Tag::p(['class' => 'form-group'], [
			Tag::label(['for' => $name], $this->label($label, $attributes)),
			Tag::make($type, array_merge(['id' => $name, 'name' => $name, 'class' => 'form-control'], $attributes)),
		]);
	}

}