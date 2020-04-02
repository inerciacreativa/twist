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
	 * @var array
	 */
	protected $ids;

	/**
	 * FormDecorator constructor.
	 *
	 * @param array $classes
	 * @param array $ids
	 */
	public function __construct(array $classes = [], array $ids = [])
	{
		$this->classes = array_merge($this->getClasses(), $classes);
		$this->ids     = array_merge($this->getIds(), $ids);
	}

	/**
	 * @param string $for
	 *
	 * @return string|null
	 */
	public function getClass(string $for): ?string
	{
		if (array_key_exists($for, $this->classes)) {
			return $this->classes[$for];
		}

		return null;
	}

	/**
	 * @param string $for
	 *
	 * @return string|null
	 */
	public function getId(string $for): ?string
	{
		if (array_key_exists($for, $this->ids)) {
			return $this->ids[$for];
		}

		return null;
	}

	/**
	 * @return array
	 */
	protected function getClasses(): array
	{
		return [
			'wrapper' => 'comment-respond',
			'title'   => 'subtitle',
			'form'    => 'comment-form',
			// Class for the field wrapper.
			'field'   => 'field',
			// Class for the <label>.
			'label'   => 'label',
			// Class for the <input> or <textarea>. If null the tag name is used.
			'input'   => null,
			// Class for the <span> wrapper of the input. If empty no wrapper will be applied.
			'control' => 'control',
			// Class for the submit button wrapper.
			'actions' => 'field actions',
			// Class for the submit <input> button.
			'submit'  => 'button is-primary is-large',
			// Class for the cancel <button>.
			'cancel'  => 'button is-warning is-small',
		];
	}

	/**
	 * @return array
	 */
	protected function getIds(): array
	{
		return [
			'wrapper' => 'respond',
			'form'    => 'comment-form',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaults(array $arguments): array
	{
		return array_merge($arguments, [
			'id_form'            => $this->ids['form'],
			'class_form'         => $this->classes['form'],
			'title_reply_before' => '<h2 class="' . $this->classes['title'] . '">',
			'title_reply_after'  => '</h2>',
			'submit_field'       => '<p class="' . $this->classes['actions'] . '">%1$s%2$s</p>',
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getTextField(string $id, string $label, array $attributes): Tag
	{
		return $this->getField('input', $id, $label, $attributes);
	}

	/**
	 * @inheritDoc
	 */
	public function getTextareaField(string $id, string $label, array $attributes): Tag
	{
		return $this->getField('textarea', $id, $label, $attributes);
	}

	/**
	 * @inheritDoc
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
	 * @inheritDoc
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

		if (isset($attributes['required'])) {
			$control['aria-required'] = 'true';
		}

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
			$label = [
				$text,
				' ',
				Tag::span([
					'class'       => 'required',
					'aria-hidden' => 'true',
				], '*'),
			];
		} else {
			$label = $text;
		}

		return Tag::label([
			'for'   => $id,
			'class' => $this->classes['label'],
		], $label);
	}

}
