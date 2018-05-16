<?php

namespace Twist\Model\Comment;

use Twist\Library\Util\Tag;

/**
 * Class CommentFormDecorator
 *
 * @package Twist\Model\Comment
 */
class CommentFormDecorator implements CommentFormDecoratorInterface
{

	/**
	 * @var string Class for the field wrapper.
	 */
	protected $field_class = 'field';

	/**
	 * @var string Class for the <label>.
	 */
	protected $label_class = 'label';

	/**
	 * @var string Class for the <input> or <textarea>.
	 *             If null the tag name is used.
	 */
	protected $input_class;

	/**
	 * @var string Class for the <span> wrapper of the input.
	 *             If empty no wrapper will be applied.
	 */
	protected $control_class = 'control';

	/**
	 * @var string Class for the submit button wrapper.
	 */
	protected $actions_class = 'field actions';

	/**
	 * @var string Class for the submit <input> button.
	 */
	protected $submit_class = 'button is-primary is-large';

	/**
	 * @var string Class for the cancel <button>.
	 *
	 */
	protected $cancel_class = 'button is-warning is-small';

	/**
	 * @inheritdoc
	 */
	public function defaults(array $arguments): array
	{
		return array_merge($arguments, [
			'title_reply_before' => '<h2 class="subtitle">',
			'title_reply_after'  => '</h2>',
			'submit_field'       => '<p class="' . $this->actions_class . '">%1$s%2$s</p>',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function text(string $id, string $label, array $attributes): Tag
	{
		return $this->field('input', $id, $label, $attributes);
	}

	/**
	 * @inheritdoc
	 */
	public function textarea(string $id, string $label, array $attributes): Tag
	{
		return $this->field('textarea', $id, $label, $attributes);
	}

	/**
	 * @inheritdoc
	 */
	public function submit(string $id, string $text): Tag
	{
		$submit = Tag::input([
			'id'    => $id,
			'name'  => $id,
			'type'  => 'submit',
			'class' => $this->submit_class,
			'value' => $text,
		]);

		if ($this->control_class) {
			$submit = Tag::span(['class' => $this->control_class], $submit);
		}

		return $submit;
	}

	/**
	 * @inheritdoc
	 */
	public function cancel(string $id, string $text, string $form): Tag
	{
		return Tag::button([
			'id'    => $id,
			'name'  => $id,
			'type'  => 'reset',
			'class' => $this->cancel_class,
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
	protected function field(string $type, string $id, string $label, array $attributes): Tag
	{
		$control = Tag::make($type, array_merge([
			'id'    => $id,
			'name'  => $id,
			'class' => $this->input_class ?? $type,
		], $attributes));

		if ($this->control_class) {
			$control = Tag::span(['class' => $this->control_class], $control);
		}

		return Tag::p(['class' => $this->field_class], [
			$this->label($id, $label),
			$control,
		]);
	}

	/**
	 * @param string $id
	 * @param string $text
	 *
	 * @return Tag
	 */
	protected function label(string $id, string $text): Tag
	{
		if (isset($attributes['required'])) {
			$label = [$text, ' ', Tag::span(['class' => 'required'], '*')];
		} else {
			$label = $text;
		}

		return Tag::label(['for' => $id, 'class' => $this->label_class], $label);
	}

}