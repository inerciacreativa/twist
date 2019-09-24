<?php

namespace Twist\Model\Comment;

use Twist\Library\Html\Tag;

/**
 * Class FormDecorator
 *
 * @package Twist\Model\Comment
 */
class FormDecorator implements FormDecoratorInterface
{

	/**
	 * @var array
	 */
	protected $classes;

	/**
	 * FormDecorator constructor.
	 *
	 * @param array $classes
	 */
	public function __construct(array $classes = [])
	{
		$this->classes = array_merge($this->getClasses(), $classes);
	}

	/**
	 * @return array
	 */
	protected function getClasses(): array
	{
		return [
			'field'   => 'field',
			// Class for the field wrapper.
			'label'   => 'label',
			// Class for the <label>.
			'input'   => null,
			// Class for the <input> or <textarea>. If null the tag name is used.
			'control' => 'control',
			// Class for the <span> wrapper of the input. If empty no wrapper will be applied.
			'actions' => 'field actions',
			// Class for the submit button wrapper.
			'submit'  => 'button is-primary is-large',
			// Class for the submit <input> button.
			'cancel'  => 'button is-warning is-small',
			// Class for the cancel <button>.
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaults(array $arguments): array
	{
		return array_merge($arguments, [
			'title_reply_before' => '<h2 class="subtitle">',
			'title_reply_after'  => '</h2>',
			'submit_field'       => '<p class="' . $this->classes['actions'] . '">%1$s%2$s</p>',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getTextField(string $id, string $label, array $attributes): Tag
	{
		return $this->getField('input', $id, $label, $attributes);
	}

	/**
	 * @inheritdoc
	 */
	public function getTextareaField(string $id, string $label, array $attributes): Tag
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
			'class' => $this->classes['submit'],
			'value' => $text,
		]);

		if ($this->classes['control']) {
			$submit = Tag::span(['class' => $this->classes['control']], $submit);
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
			'class' => $this->classes['cancel'],
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
			'class' => $this->classes['input'] ?? $type,
		], $attributes));

		if ($this->classes['control']) {
			$control = Tag::span(['class' => $this->classes['control']], $control);
		}

		return Tag::p(['class' => $this->classes['field']], [
			$this->getLabel($id, $label, $attributes),
			$control,
		]);
	}

	/**
	 * @param string $id
	 * @param string $text
	 * @param array  $attributes
	 *
	 * @return Tag
	 */
	protected function getLabel(string $id, string $text, array $attributes): Tag
	{
		if (isset($attributes['required'])) {
			$label = [$text, ' ', Tag::span(['class' => 'required'], '*')];
		} else {
			$label = $text;
		}

		return Tag::label([
			'for'   => $id,
			'class' => $this->classes['label'],
		], $label);
	}

}
