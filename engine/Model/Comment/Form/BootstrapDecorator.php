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
	public function getDefaults(array $arguments): array
	{
		return array_merge($arguments, [
			'title_reply_before' => '<h2 class="subtitle">',
			'title_reply_after'  => '</h2>',
			'submit_field'       => '<p class="form-actions">%1$s%2$s</p>',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getField(string $type, string $id, string $label, array $attributes): Tag
	{
		$labelAttributes = [
			'for' => $id,
		];

		$fieldAttributes = array_merge([
			'id'    => $id,
			'name'  => $id,
			'class' => 'form-control',
		], $attributes);

		return Tag::p(['class' => 'form-group'], [
			Tag::label($labelAttributes, $this->getLabel($label, $attributes)),
			Tag::make($type, $fieldAttributes),
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getSubmitButton(string $id, string $label): Tag
	{
		return Tag::input([
			'id'    => $id,
			'name'  => $id,
			'type'  => 'submit',
			'class' => 'btn btn-lg btn-primary',
			'value' => $label,
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getCancelButton(string $id, string $label, string $form): Tag
	{
		return Tag::button([
			'id'    => $id,
			'name'  => $id,
			'type'  => 'reset',
			'class' => 'btn btn-xs btn-danger',
		], $label);
	}

}