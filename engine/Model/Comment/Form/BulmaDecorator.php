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
	public function getDefaults(array $arguments): array
	{
		return array_merge($arguments, [
			'title_reply_before' => '<h2 class="subtitle">',
			'title_reply_after'  => '</h2>',
			'submit_field'       => '<p class="field actions">%1$s%2$s</p>',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getField(string $type, string $id, string $label, array $attributes): Tag
	{
		$labelAttributes = [
			'for'   => $id,
			'class' => 'label',
		];

		$fieldAttributes = array_merge([
			'id'    => $id,
			'name'  => $id,
			'class' => $type,
		], $attributes);

		return Tag::p(['class' => 'field'], [
			Tag::label($labelAttributes, $this->getLabel($label, $attributes)),
			Tag::span(['class' => 'control'], Tag::make($type, $fieldAttributes)),
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getSubmitButton(string $id, string $label): Tag
	{
		return Tag::span(['class' => 'control'], Tag::input([
			'id'    => $id,
			'name'  => $id,
			'type'  => 'submit',
			'class' => 'button is-primary is-large',
			'value' => $label,
		]));
	}

	/**
	 * @inheritdoc
	 */
	public function getCancelButton(string $id, string $label, string $form): Tag
	{
		return Tag::button([
			'id'    => $id,
			'name'  => $id,
			'form'  => $form,
			'type'  => 'reset',
			'class' => 'button is-danger is-small',
		], $label);
	}

}