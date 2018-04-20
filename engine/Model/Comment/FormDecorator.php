<?php

namespace Twist\Model\Comment;

use Twist\Library\Util\Tag;

/**
 * Class FormDecorator
 *
 * @package Twist\Model\Comment
 */
class FormDecorator implements FormDecoratorInterface
{

	/**
	 * @var string Class for the field wrapper.
	 */
	protected $fieldClass = 'field';

	/**
	 * @var string Class for the <label>.
	 */
	protected $labelClass = 'label';

	/**
	 * @var string Class for the <input> or <textarea>.
	 *             If null the tag name is used.
	 */
	protected $inputClass;

	/**
	 * @var string Class for the <span> wrapper of the input.
	 *             If empty no wrapper will be applied.
	 */
	protected $controlClass = 'control';

	/**
	 * @var string Class for the submit button wrapper.
	 */
	protected $actionsClass = 'field actions';

	/**
	 * @var string Class for the submit <input> button.
	 */
	protected $submitClass = 'button is-primary is-large';

	/**
	 * @var string Class for the cancel <button>.
	 *
	 */
	protected $cancelClass = 'button is-warning is-small';

	/**
	 * @inheritdoc
	 */
	public function getDefaults(array $arguments): array
	{
		return array_merge($arguments, [
			'title_reply_before' => '<h2 class="subtitle">',
			'title_reply_after'  => '</h2>',
			'submit_field'       => '<p class="' . $this->actionsClass . '">%1$s%2$s</p>',
		]);
	}

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
	 * @inheritdoc
	 */
	public function getSubmitButton(string $id, string $text): Tag
	{
		$submit = Tag::input([
			'id'    => $id,
			'name'  => $id,
			'type'  => 'submit',
			'class' => $this->submitClass,
			'value' => $text,
		]);

		if ($this->controlClass) {
			$submit = Tag::span(['class' => $this->controlClass], $submit);
		}

		return $submit;
	}

	/**
	 * @inheritdoc
	 */
	public function getCancelButton(string $id, string $text, string $form): Tag
	{
		return Tag::button([
			'id'    => $id,
			'name'  => $id,
			'type'  => 'reset',
			'class' => $this->cancelClass,
			'form'  => $form,
		], $text);
	}

	/**
	 * @param string $type
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 *
	 * @return Tag
	 */
	protected function getField(string $type, string $id, string $label, array $attributes): Tag
	{
		$control = Tag::make($type, array_merge([
			'id'    => $id,
			'name'  => $id,
			'class' => $this->inputClass ?? $type,
		], $attributes));

		if ($this->controlClass) {
			$control = Tag::span(['class' => $this->controlClass], $control);
		}

		return Tag::p(['class' => $this->fieldClass], [
			$this->getLabel($id, $label),
			$control,
		]);
	}

	/**
	 * @param string $id
	 * @param string $text
	 *
	 * @return Tag
	 */
	protected function getLabel(string $id, string $text): Tag
	{
		if (isset($attributes['required'])) {
			$label = [$text, ' ', Tag::span(['class' => 'required'], '*')];
		} else {
			$label = $text;
		}

		return Tag::label(['for' => $id, 'class' => $this->labelClass], $label);
	}

}